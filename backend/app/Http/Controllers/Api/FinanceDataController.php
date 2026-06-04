<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\FinancialAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class FinanceDataController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $revenue = (float) CustomerInvoice::query()->sum('paid_amount');
            $receivables = (float) CustomerInvoice::query()->sum('amount') - (float) CustomerInvoice::query()->sum('paid_amount');
            $payables = (float) SupplierInvoice::query()
                ->whereIn('status', ['pending', 'pending_approval', 'open', 'approved'])
                ->sum('amount');
            $cash = (float) BankAccount::query()->where('status', 'active')->sum('current_balance');

            return response()->json([
                'revenue' => $revenue,
                'expenses' => $payables,
                'cash' => $cash,
                'receivables' => $receivables,
                'active_invoices' => CustomerInvoice::query()
                    ->with('customer:id,name')
                    ->latest('invoice_date')
                    ->take(5)
                    ->get(),
                'pending_bills' => SupplierInvoice::query()
                    ->with('supplier:id,name')
                    ->latest('due_date')
                    ->take(5)
                    ->get(),
            ]);
        });
    }

    public function chartOfAccounts(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $accounts = $this->accountsWithBalances();

            return response()->json([
                'metrics' => [
                    $this->metric('Total accounts', (string) $accounts->count(), $accounts->where('is_active', true)->count().' active accounts', 'ri-git-branch-line'),
                    $this->metric('Assets', $this->money($accounts->where('type', 'asset')->sum('current_balance')), 'Current balance', 'ri-safe-line', 'success'),
                    $this->metric('Liabilities', $this->money($accounts->where('type', 'liability')->sum('current_balance')), 'Current balance', 'ri-scales-3-line', 'warning'),
                    $this->metric('Equity', $this->money($accounts->where('type', 'equity')->sum('current_balance')), 'Current balance', 'ri-funds-line', 'success'),
                ],
                'records' => $accounts->map(fn (FinancialAccount $account): array => [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => ucfirst((string) $account->type),
                    'parent' => 'General Ledger',
                    'balance' => (float) $account->current_balance,
                    'status' => $account->is_active ? 'Active' : 'Inactive',
                ])->values(),
            ]);
        });
    }

    public function journalEntries(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $entries = JournalEntry::query()->withCount('lines')->latest('posted_at')->get();

            return response()->json([
                'metrics' => [
                    $this->metric('Journal entries', (string) $entries->count(), 'All ledger batches', 'ri-book-2-line'),
                    $this->metric('Posted entries', (string) $entries->where('status', 'posted')->count(), 'Locked to the ledger', 'ri-check-double-line', 'success'),
                    $this->metric('Draft entries', (string) $entries->where('status', 'draft')->count(), 'Awaiting posting', 'ri-draft-line', 'warning'),
                ],
                'records' => $entries->map(fn (JournalEntry $entry): array => [
                    'id' => $entry->id,
                    'reference' => $entry->reference,
                    'date' => $this->date($entry->posted_at),
                    'description' => $entry->description,
                    'lines' => $entry->lines_count,
                    'status' => ucfirst((string) $entry->status),
                ])->values(),
            ]);
        });
    }

    public function generalLedger(Request $request): JsonResponse
    {
        return $this->forTenant($request, function () use ($request): JsonResponse {
            $from = $request->input('from', now()->startOfYear()->toDateString());
            $to = $request->input('to', now()->toDateString());
            $lines = JournalLine::query()
                ->with(['entry:id,reference,posted_at,description,status', 'account:id,code,name,type'])
                ->whereHas('entry', fn ($query) => $query->where('status', 'posted')->whereBetween('posted_at', [$from, $to]))
                ->latest('id')
                ->get();

            return response()->json([
                'metrics' => [
                    $this->metric('Ledger lines', (string) $lines->count(), "$from to $to", 'ri-list-check-3'),
                    $this->metric('Total debits', $this->money($lines->sum('debit')), 'Posted period', 'ri-arrow-left-down-line'),
                    $this->metric('Total credits', $this->money($lines->sum('credit')), 'Posted period', 'ri-arrow-right-up-line'),
                ],
                'records' => $lines->map(fn (JournalLine $line): array => [
                    'id' => $line->id,
                    'date' => $this->date($line->entry?->posted_at),
                    'reference' => $line->entry?->reference,
                    'account' => trim(($line->account?->code ?? '').' '.($line->account?->name ?? '')),
                    'description' => $line->memo ?: $line->entry?->description,
                    'debit' => (float) $line->debit,
                    'credit' => (float) $line->credit,
                    'status' => ucfirst((string) ($line->entry?->status ?? 'posted')),
                ])->values(),
            ]);
        });
    }

    public function vendors(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $vendors = Supplier::query()
                ->withCount('invoices')
                ->withSum(['invoices as open_balance' => fn ($query) => $query->whereIn('status', ['pending', 'pending_approval', 'open', 'approved'])], 'amount')
                ->orderBy('name')
                ->get();

            return response()->json([
                'metrics' => [
                    $this->metric('Vendors', (string) $vendors->count(), $vendors->where('status', 'active')->count().' active', 'ri-store-2-line'),
                    $this->metric('Open balance', $this->money($vendors->sum('open_balance')), 'Unpaid vendor invoices', 'ri-wallet-3-line', 'warning'),
                    $this->metric('Linked bills', (string) $vendors->sum('invoices_count'), 'Supplier invoice count', 'ri-file-list-3-line'),
                ],
                'records' => $vendors->map(fn (Supplier $vendor): array => [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'contact' => $vendor->email,
                    'phone' => $vendor->phone,
                    'bills' => $vendor->invoices_count,
                    'balance' => (float) ($vendor->open_balance ?? 0),
                    'status' => ucfirst((string) $vendor->status),
                ])->values(),
            ]);
        });
    }

    public function bills(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $bills = SupplierInvoice::query()->with('supplier:id,name')->latest('invoice_date')->get();
            $dueThisWeek = $bills->filter(fn (SupplierInvoice $bill): bool => $bill->due_date?->between(now(), now()->addWeek()) ?? false);

            return response()->json([
                'metrics' => [
                    $this->metric('Open bills', $this->money($bills->whereIn('status', ['pending', 'pending_approval', 'open', 'approved'])->sum('amount')), 'Awaiting payment', 'ri-file-text-line', 'warning'),
                    $this->metric('Due this week', $this->money($dueThisWeek->sum('amount')), $dueThisWeek->count().' bills require action', 'ri-calendar-event-line', 'danger'),
                    $this->metric('Pending approval', $this->money($bills->whereIn('status', ['pending', 'pending_approval'])->sum('amount')), 'Awaiting review', 'ri-shield-check-line'),
                ],
                'records' => $bills->map(fn (SupplierInvoice $bill): array => [
                    'id' => $bill->id,
                    'number' => $bill->invoice_number,
                    'vendor' => $bill->supplier?->name,
                    'date' => $this->date($bill->invoice_date),
                    'due' => $this->date($bill->due_date),
                    'amount' => (float) $bill->amount,
                    'status' => $this->status($bill->status),
                ])->values(),
            ]);
        });
    }

    public function payments(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $payments = Payment::query()->with('invoice.customer:id,name')->latest('paid_at')->get();

            return response()->json([
                'metrics' => [
                    $this->metric('Payments', $this->money($payments->sum('amount')), 'Recorded payments', 'ri-bank-card-line', 'success'),
                    $this->metric('Payment runs', (string) $payments->count(), 'All payment records', 'ri-play-list-add-line'),
                ],
                'records' => $payments->map(fn (Payment $payment): array => [
                    'id' => $payment->id,
                    'reference' => $payment->reference,
                    'vendor' => $payment->invoice?->customer?->name ?? 'Customer receipt',
                    'method' => $payment->method,
                    'bank' => 'Operating account',
                    'amount' => (float) $payment->amount,
                    'status' => 'Paid',
                ])->values(),
            ]);
        });
    }

    public function customers(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $customers = Customer::query()
                ->withCount('invoices')
                ->withSum('invoices as total_amount', 'amount')
                ->withSum('invoices as total_paid', 'paid_amount')
                ->orderBy('name')
                ->get();

            return response()->json([
                'metrics' => [
                    $this->metric('Total receivables', $this->money($customers->sum(fn (Customer $customer): float => (float) ($customer->total_amount ?? 0) - (float) ($customer->total_paid ?? 0))), 'Open customer balance', 'ri-wallet-3-line'),
                    $this->metric('Active customers', (string) $customers->where('status', 'active')->count(), 'Ready for invoicing', 'ri-user-heart-line', 'success'),
                    $this->metric('Invoices', (string) $customers->sum('invoices_count'), 'Customer invoice count', 'ri-file-list-3-line'),
                ],
                'records' => $customers->map(fn (Customer $customer): array => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'contact' => $customer->email,
                    'terms' => 'Net 30',
                    'credit' => 0,
                    'balance' => (float) ($customer->total_amount ?? 0) - (float) ($customer->total_paid ?? 0),
                    'status' => ucfirst((string) $customer->status),
                ])->values(),
            ]);
        });
    }

    public function receivableInvoices(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $invoices = CustomerInvoice::query()->with('customer:id,name')->latest('invoice_date')->get();
            $open = $invoices->whereIn('status', ['open', 'issued', 'partial', 'partially_paid', 'overdue']);
            $overdue = $invoices->filter(fn (CustomerInvoice $invoice): bool => ($invoice->due_date?->isPast() ?? false) && ! in_array($invoice->status, ['paid'], true));

            return response()->json([
                'metrics' => [
                    $this->metric('Outstanding invoices', $this->money($open->sum(fn (CustomerInvoice $invoice): float => (float) $invoice->amount - (float) $invoice->paid_amount)), $open->count().' open invoices', 'ri-bill-line'),
                    $this->metric('Overdue', $this->money($overdue->sum(fn (CustomerInvoice $invoice): float => (float) $invoice->amount - (float) $invoice->paid_amount)), $overdue->count().' invoices past due', 'ri-error-warning-line', 'danger'),
                    $this->metric('Collected', $this->money($invoices->sum('paid_amount')), 'Recorded customer payments', 'ri-hand-coin-line', 'success'),
                ],
                'records' => $invoices->map(fn (CustomerInvoice $invoice): array => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_name' => $invoice->customer?->name ?? 'Unknown',
                    'invoice_date' => $this->date($invoice->invoice_date),
                    'due_date' => $this->date($invoice->due_date),
                    'amount' => (float) $invoice->amount,
                    'status' => $this->status($invoice->status),
                ])->values(),
            ]);
        });
    }

    public function receipts(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $payments = Payment::query()->with('invoice.customer:id,name')->latest('paid_at')->get();

            return response()->json([
                'metrics' => [
                    $this->metric('Receipts', $this->money($payments->sum('amount')), 'Recorded customer cash', 'ri-hand-coin-line', 'success'),
                    $this->metric('Auto-match rate', $payments->count() > 0 ? '100%' : '0%', 'Matched to invoices', 'ri-link-m', 'success'),
                ],
                'records' => $payments->map(fn (Payment $payment): array => [
                    'id' => $payment->id,
                    'reference' => $payment->reference,
                    'customer' => $payment->invoice?->customer?->name,
                    'date' => $this->date($payment->paid_at),
                    'method' => $payment->method,
                    'amount' => (float) $payment->amount,
                    'status' => 'Matched',
                ])->values(),
            ]);
        });
    }

    public function bankAccounts(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $accounts = BankAccount::query()->withCount('transactions')->orderBy('bank_name')->get();
            $unreconciled = BankTransaction::query()->where('reconciliation_status', 'unreconciled')->get();

            return response()->json([
                'metrics' => [
                    $this->metric('Total cash position', $this->money($accounts->sum('current_balance')), 'Across '.$accounts->count().' accounts', 'ri-safe-2-line', 'success'),
                    $this->metric('Available balance', $this->money($accounts->sum('available_balance')), 'Available today', 'ri-bank-line'),
                    $this->metric('Unreconciled', $this->money($unreconciled->sum('amount')), $unreconciled->count().' transactions', 'ri-exchange-dollar-line', 'warning'),
                ],
                'records' => $accounts->map(fn (BankAccount $account): array => [
                    'id' => $account->id,
                    'name' => $account->account_name,
                    'bank' => $account->bank_name,
                    'type' => $account->currency,
                    'synced' => $account->updated_at?->diffForHumans() ?? 'Not synced',
                    'balance' => (float) $account->current_balance,
                    'status' => $this->status($account->status),
                ])->values(),
            ]);
        });
    }

    public function reconciliation(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $transactions = BankTransaction::query()->with('journalEntry:id,reference')->latest('transaction_date')->get();
            $matched = $transactions->where('reconciliation_status', 'reconciled');
            $unmatched = $transactions->where('reconciliation_status', 'unreconciled');

            return response()->json([
                'metrics' => [
                    $this->metric('Reconciled this month', $this->money($matched->sum('amount')), $matched->count().' transactions', 'ri-check-double-line', 'success'),
                    $this->metric('Unmatched items', (string) $unmatched->count(), $this->money($unmatched->sum('amount')).' total', 'ri-question-line', 'warning'),
                ],
                'records' => $transactions->map(fn (BankTransaction $transaction): array => [
                    'id' => $transaction->id,
                    'date' => $this->date($transaction->transaction_date),
                    'description' => $transaction->description,
                    'suggestion' => $transaction->journalEntry?->reference ?? 'No ledger match',
                    'confidence' => $transaction->journal_entry_id ? '100%' : '0%',
                    'amount' => (float) $transaction->amount,
                    'status' => $transaction->reconciliation_status === 'reconciled' ? 'Matched' : 'Pending',
                ])->values(),
            ]);
        });
    }

    public function cashForecast(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $cash = (float) BankAccount::query()->where('status', 'active')->sum('current_balance');
            $receivables = (float) CustomerInvoice::query()->sum('amount') - (float) CustomerInvoice::query()->sum('paid_amount');
            $payables = (float) SupplierInvoice::query()->whereIn('status', ['pending', 'pending_approval', 'open', 'approved'])->sum('amount');
            $forecast30 = $cash + ($receivables * 0.35) - ($payables * 0.35);
            $forecast90 = $cash + $receivables - $payables;

            return response()->json([
                'metrics' => [
                    ['label' => 'Current Cash', 'value' => $this->money($cash), 'note' => 'Available today'],
                    ['label' => '30-Day Forecast', 'value' => $this->money($forecast30), 'note' => 'Based on open items'],
                    ['label' => '90-Day Forecast', 'value' => $this->money($forecast90), 'note' => 'Based on open items'],
                ],
                'sections' => [
                    ['title' => 'Expected Inflows', 'rows' => [['label' => 'Open customer invoices', 'value' => $this->money($receivables)]], 'total' => $this->money($receivables)],
                    ['title' => 'Expected Outflows', 'rows' => [['label' => 'Open supplier invoices', 'value' => '-'.$this->money($payables)]], 'total' => '-'.$this->money($payables)],
                    ['title' => 'Forecast Cash Position', 'rows' => [], 'totalLabel' => 'Projected Cash at 90 Days', 'total' => $this->money($forecast90)],
                ],
            ]);
        });
    }

    public function profitLoss(Request $request): JsonResponse
    {
        return $this->forTenant($request, fn (): JsonResponse => response()->json($this->profitLossPayload()));
    }

    public function balanceSheet(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $accounts = $this->accountsWithBalances();
            $assets = $accounts->where('type', 'asset');
            $liabilities = $accounts->where('type', 'liability');
            $equity = $accounts->where('type', 'equity');

            return response()->json([
                'metrics' => [
                    ['label' => 'Total Assets', 'value' => $this->money($assets->sum('current_balance')), 'note' => 'Current balance'],
                    ['label' => 'Total Liabilities', 'value' => $this->money($liabilities->sum('current_balance')), 'note' => 'Current balance'],
                    ['label' => 'Total Equity', 'value' => $this->money($equity->sum('current_balance')), 'note' => 'Current balance'],
                ],
                'sections' => [
                    $this->accountSection('Assets', $assets),
                    $this->accountSection('Liabilities', $liabilities),
                    $this->accountSection('Equity', $equity),
                ],
            ]);
        });
    }

    public function cashFlow(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $inflows = (float) BankTransaction::query()->where('type', 'credit')->sum('amount');
            $outflows = (float) BankTransaction::query()->where('type', 'debit')->sum('amount');
            $net = $inflows - $outflows;

            return response()->json([
                'metrics' => [
                    ['label' => 'Operating Cash Flow', 'value' => $this->money($net), 'note' => 'From bank feed activity'],
                    ['label' => 'Cash Inflows', 'value' => $this->money($inflows), 'note' => 'Credits'],
                    ['label' => 'Cash Outflows', 'value' => $this->money($outflows), 'note' => 'Debits'],
                ],
                'sections' => [
                    ['title' => 'Operating Activities', 'rows' => [['label' => 'Bank credits', 'value' => $this->money($inflows)], ['label' => 'Bank debits', 'value' => '-'.$this->money($outflows)]], 'total' => $this->money($net)],
                ],
            ]);
        });
    }

    public function auditTrail(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $logs = AuditLog::query()->with('user:id,name')->latest()->take(100)->get();

            return response()->json([
                'metrics' => [
                    $this->metric('Audit events', (string) $logs->count(), 'Latest records', 'ri-pulse-line'),
                    $this->metric('Financial changes', (string) $logs->where('auditable_type', '!=', null)->count(), 'Fully traceable', 'ri-file-shield-2-line', 'success'),
                ],
                'records' => $logs->map(fn (AuditLog $log): array => [
                    'id' => $log->id,
                    'time' => $this->date($log->created_at),
                    'user' => $log->user?->name ?? 'System',
                    'action' => $log->action,
                    'record' => class_basename((string) $log->auditable_type).' #'.$log->auditable_id,
                    'ip' => $log->ip_address ?? 'N/A',
                    'status' => 'Completed',
                ])->values(),
            ]);
        });
    }

    public function users(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $users = User::query()->latest()->get();

            return response()->json([
                'metrics' => [
                    $this->metric('Active users', (string) $users->count(), 'Tenant users', 'ri-team-line', 'success'),
                    $this->metric('Pending invitations', '0', 'No pending invites', 'ri-mail-add-line'),
                ],
                'records' => $users->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'Standard User',
                    'modules' => 'Finance',
                    'lastActive' => $user->updated_at?->diffForHumans(),
                    'status' => 'Active',
                ])->values(),
            ]);
        });
    }

    public function roles(Request $request): JsonResponse
    {
        return $this->forTenant($request, function (): JsonResponse {
            $roles = Role::query()->latest()->get();

            return response()->json([
                'metrics' => [
                    $this->metric('Configured roles', (string) $roles->count(), 'Access profiles', 'ri-shield-user-line'),
                    $this->metric('Segregation conflicts', '0', 'No conflicts detected', 'ri-shield-check-line', 'success'),
                ],
                'records' => $roles->map(fn (Role $role): array => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description ?? 'Role permissions',
                    'users' => '0 users',
                    'modules' => 'Finance',
                    'updated' => $this->date($role->updated_at),
                    'status' => 'Active',
                ])->values(),
            ]);
        });
    }

    /**
     * @template TReturn
     * @param callable(): TReturn $callback
     * @return TReturn
     */
    private function forTenant(Request $request, callable $callback): mixed
    {
        $tenantId = $request->user()?->tenant_id
            ?? Tenant::query()->where('slug', 'demo')->value('id')
            ?? Tenant::query()->value('id');

        if ($tenantId !== null) {
            TenantContext::set((int) $tenantId);
        }

        try {
            return $callback();
        } finally {
            TenantContext::clear();
        }
    }

    private function accountsWithBalances(): Collection
    {
        return FinancialAccount::query()
            ->withSum(['journalLines as debit_total'], 'debit')
            ->withSum(['journalLines as credit_total'], 'credit')
            ->orderBy('code')
            ->get()
            ->map(function (FinancialAccount $account): FinancialAccount {
                $debits = (float) ($account->debit_total ?? 0);
                $credits = (float) ($account->credit_total ?? 0);
                $account->current_balance = $account->normal_balance === 'credit'
                    ? $credits - $debits
                    : $debits - $credits;

                return $account;
            });
    }

    private function profitLossPayload(): array
    {
        $accounts = $this->accountsWithBalances();
        $revenue = $accounts->where('type', 'revenue');
        $expenses = $accounts->where('type', 'expense');
        $revenueTotal = (float) $revenue->sum('current_balance');
        $expenseTotal = (float) $expenses->sum('current_balance');

        return [
            'metrics' => [
                ['label' => 'Total Revenue', 'value' => $this->money($revenueTotal), 'note' => 'Posted ledger revenue'],
                ['label' => 'Operating Expenses', 'value' => $this->money($expenseTotal), 'note' => 'Posted ledger expenses'],
                ['label' => 'Net Income', 'value' => $this->money($revenueTotal - $expenseTotal), 'note' => 'Revenue less expenses'],
            ],
            'sections' => [
                $this->accountSection('Revenue', $revenue),
                $this->accountSection('Operating Expenses', $expenses),
                ['title' => 'Net Income', 'rows' => [], 'totalLabel' => 'Net Income', 'total' => $this->money($revenueTotal - $expenseTotal)],
            ],
        ];
    }

    private function accountSection(string $title, Collection $accounts): array
    {
        return [
            'title' => $title,
            'rows' => $accounts->map(fn (FinancialAccount $account): array => [
                'label' => $account->name,
                'value' => $this->money((float) $account->current_balance),
            ])->values(),
            'total' => $this->money((float) $accounts->sum('current_balance')),
        ];
    }

    private function metric(string $label, string $value, string $trend, string $icon, ?string $tone = null): array
    {
        return array_filter(compact('label', 'value', 'trend', 'icon', 'tone'), fn ($value): bool => $value !== null);
    }

    private function money(float|int|string|null $amount): string
    {
        return 'GHC '.number_format((float) ($amount ?? 0), 2);
    }

    private function date(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Carbon::parse($value)->format('M d, Y');
    }

    private function status(?string $status): string
    {
        return str((string) $status)->replace('_', ' ')->title()->toString();
    }
}
