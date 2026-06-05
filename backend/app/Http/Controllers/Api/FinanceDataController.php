<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\FinancialAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
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
            $totalEntries = JournalEntry::query()->count();
            $postedEntries = JournalEntry::query()->where('status', 'posted')->count();
            $draftEntries = JournalEntry::query()->where('status', 'draft')->count();

            $entries = JournalEntry::query()->withCount('lines')->latest('posted_at')->paginate(per_page: 20)->withQueryString();

            return response()->json([
                'metrics' => [
                    $this->metric('Journal entries', (string) $totalEntries, 'All ledger batches', 'ri-book-2-line'),
                    $this->metric('Posted entries', (string) $postedEntries, 'Locked to the ledger', 'ri-check-double-line', 'success'),
                    $this->metric('Draft entries', (string) $draftEntries, 'Awaiting posting', 'ri-draft-line', 'warning'),
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

            $baseQuery = JournalLine::query()
                ->whereHas('entry', fn ($query) => $query->where('status', 'posted')->whereBetween('posted_at', [$from, $to]));

            $totalDebits = (float) (clone $baseQuery)->sum('debit');
            $totalCredits = (float) (clone $baseQuery)->sum('credit');
            $totalLines = (int) (clone $baseQuery)->count();

            $lines = (clone $baseQuery)
                ->with(['entry:id,reference,posted_at,description,status', 'account:id,code,name,type'])
                ->latest('id')
                ->paginate(per_page: 50)
                ->withQueryString();

            return response()->json([
                'metrics' => [
                    $this->metric('Ledger lines', (string) $totalLines, "$from to $to", 'ri-list-check-3'),
                    $this->metric('Total debits', $this->money($totalDebits), 'Posted period', 'ri-arrow-left-down-line'),
                    $this->metric('Total credits', $this->money($totalCredits), 'Posted period', 'ri-arrow-right-up-line'),
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
                'pagination' => [
                    'current_page' => $lines->currentPage(),
                    'last_page' => $lines->lastPage(),
                    'per_page' => $lines->perPage(),
                    'total' => $lines->total(),
                ],
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
                ->paginate(per_page: 20)->withQueryString();

            $totalVendors = Supplier::query()->count();
            $activeVendors = Supplier::query()->where('status', 'active')->count();
            $openBalance = (float) SupplierInvoice::query()->whereIn('status', ['pending', 'pending_approval', 'open', 'approved'])->sum('amount');
            $totalBills = (int) SupplierInvoice::query()->count();

            return response()->json([
                'metrics' => [
                    $this->metric('Vendors', (string) $totalVendors, $activeVendors.' active', 'ri-store-2-line'),
                    $this->metric('Open balance', $this->money($openBalance), 'Unpaid vendor invoices', 'ri-wallet-3-line', 'warning'),
                    $this->metric('Linked bills', (string) $totalBills, 'Supplier invoice count', 'ri-file-list-3-line'),
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
            $bills = SupplierInvoice::query()->with('supplier:id,name')->latest('invoice_date')->paginate(per_page: 20)->withQueryString();
            $totalOpen = (float) SupplierInvoice::query()->whereIn('status', ['pending', 'pending_approval', 'open', 'approved'])->sum('amount');
            $totalDueThisWeek = (float) SupplierInvoice::query()
                ->whereIn('status', ['pending', 'pending_approval', 'open', 'approved'])
                ->whereBetween('due_date', [now()->toDateString(), now()->addWeek()->toDateString()])
                ->sum('amount');
            $dueThisWeekCount = SupplierInvoice::query()
                ->whereIn('status', ['pending', 'pending_approval', 'open', 'approved'])
                ->whereBetween('due_date', [now()->toDateString(), now()->addWeek()->toDateString()])
                ->count();
            $pendingAmount = (float) SupplierInvoice::query()->whereIn('status', ['pending', 'pending_approval'])->sum('amount');

            return response()->json([
                'metrics' => [
                    $this->metric('Open bills', $this->money($totalOpen), 'Awaiting payment', 'ri-file-text-line', 'warning'),
                    $this->metric('Due this week', $this->money($totalDueThisWeek), $dueThisWeekCount.' bills require action', 'ri-calendar-event-line', 'danger'),
                    $this->metric('Pending approval', $this->money($pendingAmount), 'Awaiting review', 'ri-shield-check-line'),
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
            $payments = Payment::query()->with('invoice.customer:id,name')->latest('paid_at')->paginate(per_page: 20)->withQueryString();
            $totalAmount = (float) Payment::query()->sum('amount');
            $totalCount = Payment::query()->count();

            return response()->json([
                'metrics' => [
                    $this->metric('Payments', $this->money($totalAmount), 'Recorded payments', 'ri-bank-card-line', 'success'),
                    $this->metric('Payment runs', (string) $totalCount, 'All payment records', 'ri-play-list-add-line'),
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
                ->paginate(per_page: 20)->withQueryString();

            $totalReceivables = (float) CustomerInvoice::query()->sum('amount') - (float) CustomerInvoice::query()->sum('paid_amount');
            $activeCount = Customer::query()->where('status', 'active')->count();
            $invoiceCount = (int) CustomerInvoice::query()->count();

            return response()->json([
                'metrics' => [
                    $this->metric('Total receivables', $this->money($totalReceivables), 'Open customer balance', 'ri-wallet-3-line'),
                    $this->metric('Active customers', (string) $activeCount, 'Ready for invoicing', 'ri-user-heart-line', 'success'),
                    $this->metric('Invoices', (string) $invoiceCount, 'Customer invoice count', 'ri-file-list-3-line'),
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
            $invoices = CustomerInvoice::query()->with('customer:id,name')->latest('invoice_date')->paginate(per_page: 20)->withQueryString();
            $openAmount = (float) CustomerInvoice::query()->whereIn('status', ['open', 'issued', 'partial', 'partially_paid', 'overdue'])
                ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')->value('total');
            $openCount = CustomerInvoice::query()->whereIn('status', ['open', 'issued', 'partial', 'partially_paid', 'overdue'])->count();
            $overdueAmount = (float) CustomerInvoice::query()
                ->whereDate('due_date', '<', now()->toDateString())
                ->whereNotIn('status', ['paid'])
                ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')->value('total');
            $overdueCount = CustomerInvoice::query()
                ->whereDate('due_date', '<', now()->toDateString())
                ->whereNotIn('status', ['paid'])->count();
            $collected = (float) CustomerInvoice::query()->sum('paid_amount');

            return response()->json([
                'metrics' => [
                    $this->metric('Outstanding invoices', $this->money($openAmount), $openCount.' open invoices', 'ri-bill-line'),
                    $this->metric('Overdue', $this->money($overdueAmount), $overdueCount.' invoices past due', 'ri-error-warning-line', 'danger'),
                    $this->metric('Collected', $this->money($collected), 'Recorded customer payments', 'ri-hand-coin-line', 'success'),
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
            $payments = Payment::query()->with('invoice.customer:id,name')->latest('paid_at')->paginate(per_page: 20)->withQueryString();
            $totalAmount = (float) Payment::query()->sum('amount');
            $totalCount = Payment::query()->count();

            return response()->json([
                'metrics' => [
                    $this->metric('Receipts', $this->money($totalAmount), 'Recorded customer cash', 'ri-hand-coin-line', 'success'),
                    $this->metric('Auto-match rate', $totalCount > 0 ? '100%' : '0%', 'Matched to invoices', 'ri-link-m', 'success'),
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
            $accounts = BankAccount::query()->withCount('transactions')->orderBy('bank_name')->paginate(per_page: 20)->withQueryString();
            $totalCash = (float) BankAccount::query()->sum('current_balance');
            $totalAvailable = (float) BankAccount::query()->sum('available_balance');
            $accountCount = BankAccount::query()->count();
            $unreconciledAmount = (float) BankTransaction::query()->where('reconciliation_status', 'unreconciled')->sum('amount');
            $unreconciledCount = BankTransaction::query()->where('reconciliation_status', 'unreconciled')->count();

            return response()->json([
                'metrics' => [
                    $this->metric('Total cash position', $this->money($totalCash), 'Across '.$accountCount.' accounts', 'ri-safe-2-line', 'success'),
                    $this->metric('Available balance', $this->money($totalAvailable), 'Available today', 'ri-bank-line'),
                    $this->metric('Unreconciled', $this->money($unreconciledAmount), $unreconciledCount.' transactions', 'ri-exchange-dollar-line', 'warning'),
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
            $transactions = BankTransaction::query()->with('journalEntry:id,reference')->latest('transaction_date')->paginate(per_page: 20)->withQueryString();
            $matchedAmount = (float) BankTransaction::query()->where('reconciliation_status', 'reconciled')->sum('amount');
            $matchedCount = BankTransaction::query()->where('reconciliation_status', 'reconciled')->count();
            $unmatchedCount = BankTransaction::query()->where('reconciliation_status', 'unreconciled')->count();
            $unmatchedAmount = (float) BankTransaction::query()->where('reconciliation_status', 'unreconciled')->sum('amount');

            return response()->json([
                'metrics' => [
                    $this->metric('Reconciled this month', $this->money($matchedAmount), $matchedCount.' transactions', 'ri-check-double-line', 'success'),
                    $this->metric('Unmatched items', (string) $unmatchedCount, $this->money($unmatchedAmount).' total', 'ri-question-line', 'warning'),
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
            $totalEvents = AuditLog::query()->count();
            $financialChanges = AuditLog::query()->whereNotNull('auditable_type')->count();

            $logs = AuditLog::query()->with('user:id,name')->latest()->paginate(per_page: 50)->withQueryString();

            return response()->json([
                'metrics' => [
                    $this->metric('Audit events', (string) $totalEvents, 'Latest records', 'ri-pulse-line'),
                    $this->metric('Financial changes', (string) $financialChanges, 'Fully traceable', 'ri-file-shield-2-line', 'success'),
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
        return $this->forTenant($request, function () use ($request): JsonResponse {
            $tenantId = $request->user()->tenant_id;
            $users = User::query()->where('tenant_id', $tenantId)->latest()->paginate(per_page: 20)->withQueryString();
            $totalUsers = User::query()->where('tenant_id', $tenantId)->count();

            return response()->json([
                'metrics' => [
                    $this->metric('Active users', (string) $totalUsers, 'Tenant users', 'ri-team-line', 'success'),
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
            $roles = Role::query()->latest()->paginate(per_page: 20)->withQueryString();
            $totalRoles = Role::query()->count();

            return response()->json([
                'metrics' => [
                    $this->metric('Configured roles', (string) $totalRoles, 'Access profiles', 'ri-shield-user-line'),
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

    // ─── Accounting CRUD ─────────────────────────────────────

    public function journalEntry(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        return $this->forTenant($request, function () use ($journalEntry): JsonResponse {
            $journalEntry->load('lines.account', 'lines');
            return response()->json(['record' => [
                'id' => $journalEntry->id,
                'entryNo' => 'JE-'.str_pad((string) $journalEntry->id, 4, '0', STR_PAD_LEFT),
                'date' => $this->date($journalEntry->posted_at),
                'description' => $journalEntry->description,
                'reference' => $journalEntry->reference,
                'status' => $journalEntry->status,
                'lines' => $journalEntry->lines->map(fn (JournalLine $line): array => [
                    'accountCode' => $line->account?->code,
                    'accountName' => $line->account?->name,
                    'debit' => (float) $line->debit,
                    'credit' => (float) $line->credit,
                ]),
            ]]);
        });
    }

    public function createJournalEntry(Request $request): JsonResponse
    {
        return $this->forTenant($request, function () use ($request): JsonResponse {
            $validated = $request->validate([
                'date' => 'required|date',
                'description' => 'required|string|max:500',
                'reference' => 'nullable|string|max:100',
                'status' => 'nullable|string|in:draft,pending,posted',
                'lines' => 'required|array|min:1',
                'lines.*.accountCode' => 'required|string',
                'lines.*.debit' => 'nullable|numeric|min:0',
                'lines.*.credit' => 'nullable|numeric|min:0',
            ]);

            $entry = JournalEntry::query()->create([
                'tenant_id' => TenantContext::requireId(),
                'description' => $validated['description'],
                'reference' => $validated['reference'] ?? 'REF-'.now()->timestamp,
                'status' => $validated['status'] ?? 'draft',
                'posted_at' => $validated['date'].' 00:00:00',
                'created_by' => $request->user()?->id,
            ]);

            foreach ($validated['lines'] as $line) {
                $account = FinancialAccount::query()
                    ->where('code', $line['accountCode'])
                    ->where('tenant_id', TenantContext::requireId())
                    ->first();
                JournalLine::query()->create([
                    'tenant_id' => TenantContext::requireId(),
                    'journal_entry_id' => $entry->id,
                    'financial_account_id' => $account?->id,
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                ]);
            }

            return response()->json(['record' => $entry->fresh()], 201);
        });
    }

    public function updateJournalEntry(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        return $this->forTenant($request, function () use ($request, $journalEntry): JsonResponse {
            $validated = $request->validate([
                'date' => 'nullable|date',
                'description' => 'nullable|string|max:500',
                'reference' => 'nullable|string|max:100',
                'status' => 'nullable|string|in:draft,pending,posted,voided',
            ]);

            $journalEntry->update(array_filter([
                'description' => $validated['description'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'status' => $validated['status'] ?? null,
                'posted_at' => $validated['date'] ?? null,
            ], fn ($v) => $v !== null));

            return response()->json(['record' => $journalEntry->fresh()]);
        });
    }

    public function deleteJournalEntry(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        return $this->forTenant($request, function () use ($journalEntry): JsonResponse {
            $journalEntry->lines()->delete();
            $journalEntry->delete();
            return response()->json(['message' => 'Deleted']);
        });
    }

    public function createAccount(Request $request): JsonResponse
    {
        return $this->forTenant($request, function () use ($request): JsonResponse {
            $validated = $request->validate([
                'code' => 'required|string|max:20|unique:financial_accounts,code',
                'name' => 'required|string|max:200',
                'type' => 'required|string|in:asset,liability,equity,revenue,expense',
                'is_active' => 'nullable|boolean',
            ]);
            $account = FinancialAccount::query()->create([
                'tenant_id' => TenantContext::requireId(),
                'code' => $validated['code'],
                'name' => $validated['name'],
                'type' => $validated['type'],
                'normal_balance' => in_array($validated['type'], ['liability', 'equity', 'revenue']) ? 'credit' : 'debit',
                'is_active' => $validated['is_active'] ?? true,
            ]);
            return response()->json(['record' => $account], 201);
        });
    }

    public function updateAccount(Request $request, FinancialAccount $financialAccount): JsonResponse
    {
        return $this->forTenant($request, function () use ($request, $financialAccount): JsonResponse {
            $validated = $request->validate([
                'code' => 'nullable|string|max:20|unique:financial_accounts,code,'.$financialAccount->id,
                'name' => 'nullable|string|max:200',
                'type' => 'nullable|string|in:asset,liability,equity,revenue,expense',
                'is_active' => 'nullable|boolean',
            ]);
            $financialAccount->update(array_filter($validated, fn ($v) => $v !== null));
            return response()->json(['record' => $financialAccount->fresh()]);
        });
    }

    public function deleteAccount(Request $request, FinancialAccount $financialAccount): JsonResponse
    {
        return $this->forTenant($request, function () use ($financialAccount): JsonResponse {
            if ($financialAccount->journalLines()->exists()) {
                return response()->json(['message' => 'Account has journal lines'], 409);
            }
            $financialAccount->delete();
            return response()->json(['message' => 'Deleted']);
        });
    }

    // ─── Payables CRUD ───────────────────────────────────────

    public function vendor(Request $request, Supplier $supplier): JsonResponse
    {
        return $this->forTenant($request, function () use ($supplier): JsonResponse {
            return response()->json(['record' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'category' => $supplier->category,
                'tax_id' => $supplier->tax_id,
                'payment_terms' => $supplier->payment_terms ?? 'Net 30',
                'open_balance' => (float) SupplierInvoice::query()->where('supplier_id', $supplier->id)->whereIn('status', ['pending', 'pending_approval', 'open', 'approved'])->sum('amount'),
                'total_paid' => (float) Payment::query()->whereHas('invoice', fn ($q) => $q->where('supplier_id', $supplier->id))->sum('amount'),
                'status' => $supplier->status,
            ]]);
        });
    }

    public function createVendor(Request $request): JsonResponse
    {
        return $this->forTenant($request, function () use ($request): JsonResponse {
            $validated = $request->validate([
                'name' => 'required|string|max:200',
                'email' => 'nullable|email|max:200',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:500',
                'category' => 'nullable|string|max:100',
            ]);
            $vendor = Supplier::query()->create([
                'tenant_id' => TenantContext::requireId(),
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'category' => $validated['category'] ?? null,
                'status' => 'active',
            ]);
            return response()->json(['record' => $vendor], 201);
        });
    }

    public function updateVendor(Request $request, Supplier $supplier): JsonResponse
    {
        return $this->forTenant($request, function () use ($request, $supplier): JsonResponse {
            $validated = $request->validate([
                'name' => 'nullable|string|max:200',
                'email' => 'nullable|email|max:200',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:500',
                'category' => 'nullable|string|max:100',
                'status' => 'nullable|string|in:active,inactive',
            ]);
            $supplier->update(array_filter($validated, fn ($v) => $v !== null));
            return response()->json(['record' => $supplier->fresh()]);
        });
    }

    public function deleteVendor(Request $request, Supplier $supplier): JsonResponse
    {
        return $this->forTenant($request, function () use ($supplier): JsonResponse {
            if ($supplier->invoices()->exists()) {
                return response()->json(['message' => 'Vendor has linked invoices'], 409);
            }
            $supplier->delete();
            return response()->json(['message' => 'Deleted']);
        });
    }

    public function bill(Request $request, SupplierInvoice $supplierInvoice): JsonResponse
    {
        return $this->forTenant($request, function () use ($supplierInvoice): JsonResponse {
            $supplierInvoice->load('supplier:id,name');
            return response()->json(['record' => $supplierInvoice]);
        });
    }

    public function createBill(Request $request): JsonResponse
    {
        return $this->forTenant($request, function () use ($request): JsonResponse {
            $validated = $request->validate([
                'vendor' => 'required|string|max:200',
                'number' => 'required|string|max:50',
                'date' => 'required|date',
                'due' => 'required|date',
                'amount' => 'required|numeric|min:0',
                'status' => 'nullable|string',
            ]);
            $supplier = Supplier::query()->firstOrCreate(
                ['name' => $validated['vendor'], 'tenant_id' => TenantContext::requireId()],
                ['tenant_id' => TenantContext::requireId(), 'status' => 'active'],
            );
            $bill = SupplierInvoice::query()->create([
                'tenant_id' => TenantContext::requireId(),
                'supplier_id' => $supplier->id,
                'invoice_number' => $validated['number'],
                'invoice_date' => $validated['date'],
                'due_date' => $validated['due'],
                'amount' => $validated['amount'],
                'status' => $validated['status'] ?? 'pending',
            ]);
            return response()->json(['record' => $bill], 201);
        });
    }

    public function updateBill(Request $request, SupplierInvoice $supplierInvoice): JsonResponse
    {
        return $this->forTenant($request, function () use ($request, $supplierInvoice): JsonResponse {
            $validated = $request->validate([
                'amount' => 'nullable|numeric|min:0',
                'status' => 'nullable|string',
                'due' => 'nullable|date',
            ]);
            $supplierInvoice->update(array_filter([
                'amount' => $validated['amount'] ?? null,
                'due_date' => $validated['due'] ?? null,
                'status' => $validated['status'] ?? null,
            ], fn ($v) => $v !== null));
            return response()->json(['record' => $supplierInvoice->fresh()]);
        });
    }

    public function deleteBill(Request $request, SupplierInvoice $supplierInvoice): JsonResponse
    {
        return $this->forTenant($request, function () use ($supplierInvoice): JsonResponse {
            $supplierInvoice->delete();
            return response()->json(['message' => 'Deleted']);
        });
    }

    public function payment(Request $request, Payment $payment): JsonResponse
    {
        return $this->forTenant($request, function () use ($payment): JsonResponse {
            $payment->load('invoice');
            return response()->json(['record' => $payment]);
        });
    }

    public function createPayment(Request $request): JsonResponse
    {
        return $this->forTenant($request, function () use ($request): JsonResponse {
            $validated = $request->validate([
                'vendor' => 'nullable|string|max:200',
                'amount' => 'required|numeric|min:0',
                'method' => 'nullable|string|max:50',
                'bank' => 'nullable|string|max:200',
                'date' => 'nullable|date',
                'reference' => 'nullable|string|max:100',
                'status' => 'nullable|string',
            ]);
            $payment = Payment::query()->create([
                'tenant_id' => TenantContext::requireId(),
                'amount' => $validated['amount'],
                'method' => $validated['method'] ?? 'Bank Transfer',
                'reference' => $validated['reference'] ?? null,
                'paid_at' => $validated['date'] ?? now(),
                'status' => $validated['status'] ?? 'Paid',
            ]);
            return response()->json(['record' => $payment], 201);
        });
    }

    public function updatePayment(Request $request, Payment $payment): JsonResponse
    {
        return $this->forTenant($request, function () use ($request, $payment): JsonResponse {
            $validated = $request->validate([
                'amount' => 'nullable|numeric|min:0',
                'method' => 'nullable|string|max:50',
                'status' => 'nullable|string',
            ]);
            $payment->update(array_filter($validated, fn ($v) => $v !== null));
            return response()->json(['record' => $payment->fresh()]);
        });
    }

    public function deletePayment(Request $request, Payment $payment): JsonResponse
    {
        return $this->forTenant($request, function () use ($payment): JsonResponse {
            $payment->delete();
            return response()->json(['message' => 'Deleted']);
        });
    }

    // ─── Receivables CRUD ────────────────────────────────────

    public function customer(Request $request, Customer $customer): JsonResponse
    {
        return $this->forTenant($request, function () use ($customer): JsonResponse {
            return response()->json(['record' => $customer]);
        });
    }

    public function createCustomer(Request $request): JsonResponse
    {
        return $this->forTenant($request, function () use ($request): JsonResponse {
            $validated = $request->validate([
                'name' => 'required|string|max:200',
                'email' => 'nullable|email|max:200',
                'contact' => 'nullable|string|max:100',
                'credit_limit' => 'nullable|numeric|min:0',
                'terms' => 'nullable|string|max:50',
                'status' => 'nullable|string',
            ]);
            $customer = Customer::query()->create([
                'tenant_id' => TenantContext::requireId(),
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'contact_person' => $validated['contact'] ?? null,
                'credit_limit' => $validated['credit_limit'] ?? 0,
                'payment_terms' => $validated['terms'] ?? 'Net 30',
                'status' => $validated['status'] ?? 'active',
            ]);
            return response()->json(['record' => $customer], 201);
        });
    }

    public function updateCustomer(Request $request, Customer $customer): JsonResponse
    {
        return $this->forTenant($request, function () use ($request, $customer): JsonResponse {
            $validated = $request->validate([
                'name' => 'nullable|string|max:200',
                'email' => 'nullable|email|max:200',
                'contact' => 'nullable|string|max:100',
                'credit_limit' => 'nullable|numeric|min:0',
                'status' => 'nullable|string',
            ]);
            $customer->update(array_filter([
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
                'contact_person' => $validated['contact'] ?? null,
                'credit_limit' => $validated['credit_limit'] ?? null,
                'status' => $validated['status'] ?? null,
            ], fn ($v) => $v !== null));
            return response()->json(['record' => $customer->fresh()]);
        });
    }

    public function deleteCustomer(Request $request, Customer $customer): JsonResponse
    {
        return $this->forTenant($request, function () use ($customer): JsonResponse {
            if ($customer->invoices()->exists()) {
                return response()->json(['message' => 'Customer has linked invoices'], 409);
            }
            $customer->delete();
            return response()->json(['message' => 'Deleted']);
        });
    }

    public function createReceivableInvoice(Request $request): JsonResponse
    {
        return $this->forTenant($request, function () use ($request): JsonResponse {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'invoice_number' => 'required|string|max:50',
                'invoice_date' => 'required|date',
                'due_date' => 'required|date',
                'amount' => 'required|numeric|min:0',
                'status' => 'nullable|string',
            ]);
            $invoice = CustomerInvoice::query()->create([
                'tenant_id' => TenantContext::requireId(),
                'customer_id' => $validated['customer_id'],
                'invoice_number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'amount' => $validated['amount'],
                'status' => $validated['status'] ?? 'open',
            ]);
            return response()->json(['record' => $invoice], 201);
        });
    }

    public function updateReceivableInvoice(Request $request, CustomerInvoice $customerInvoice): JsonResponse
    {
        return $this->forTenant($request, function () use ($request, $customerInvoice): JsonResponse {
            $validated = $request->validate([
                'amount' => 'nullable|numeric|min:0',
                'status' => 'nullable|string',
            ]);
            $customerInvoice->update(array_filter($validated, fn ($v) => $v !== null));
            return response()->json(['record' => $customerInvoice->fresh()]);
        });
    }

    public function deleteReceivableInvoice(Request $request, CustomerInvoice $customerInvoice): JsonResponse
    {
        return $this->forTenant($request, function () use ($customerInvoice): JsonResponse {
            $customerInvoice->delete();
            return response()->json(['message' => 'Deleted']);
        });
    }

    public function receipt(Request $request, Payment $payment): JsonResponse
    {
        return $this->forTenant($request, function () use ($payment): JsonResponse {
            $payment->load('invoice.customer');
            return response()->json(['record' => $payment]);
        });
    }

    public function createReceipt(Request $request): JsonResponse
    {
        return $this->forTenant($request, function () use ($request): JsonResponse {
            $validated = $request->validate([
                'customer' => 'nullable|string|max:200',
                'amount' => 'required|numeric|min:0',
                'method' => 'nullable|string|max:50',
                'date' => 'nullable|date',
                'reference' => 'nullable|string|max:100',
                'status' => 'nullable|string',
            ]);
            $receipt = Payment::query()->create([
                'tenant_id' => TenantContext::requireId(),
                'amount' => $validated['amount'],
                'method' => $validated['method'] ?? 'Mobile Money',
                'reference' => $validated['reference'] ?? null,
                'paid_at' => $validated['date'] ?? now(),
                'status' => $validated['status'] ?? 'Matched',
            ]);
            return response()->json(['record' => $receipt], 201);
        });
    }

    public function updateReceipt(Request $request, Payment $payment): JsonResponse
    {
        return $this->forTenant($request, function () use ($request, $payment): JsonResponse {
            $validated = $request->validate([
                'amount' => 'nullable|numeric|min:0',
                'method' => 'nullable|string|max:50',
                'status' => 'nullable|string',
            ]);
            $payment->update(array_filter($validated, fn ($v) => $v !== null));
            return response()->json(['record' => $payment->fresh()]);
        });
    }

    public function deleteReceipt(Request $request, Payment $payment): JsonResponse
    {
        return $this->forTenant($request, function () use ($payment): JsonResponse {
            $payment->delete();
            return response()->json(['message' => 'Deleted']);
        });
    }

    // ─── Treasury CRUD ──────────────────────────────────────

    public function createBankAccount(Request $request): JsonResponse
    {
        return $this->forTenant($request, function () use ($request): JsonResponse {
            $validated = $request->validate([
                'name' => 'required|string|max:200',
                'bank' => 'required|string|max:200',
                'type' => 'nullable|string|max:50',
                'balance' => 'nullable|numeric',
                'status' => 'nullable|string',
            ]);
            $account = BankAccount::query()->create([
                'tenant_id' => TenantContext::requireId(),
                'account_name' => $validated['name'],
                'bank_name' => $validated['bank'],
                'currency' => $validated['type'] ?? 'GHS',
                'current_balance' => $validated['balance'] ?? 0,
                'available_balance' => $validated['balance'] ?? 0,
                'status' => $validated['status'] ?? 'active',
            ]);
            return response()->json(['record' => $account], 201);
        });
    }

    public function updateBankAccount(Request $request, BankAccount $bankAccount): JsonResponse
    {
        return $this->forTenant($request, function () use ($request, $bankAccount): JsonResponse {
            $validated = $request->validate([
                'name' => 'nullable|string|max:200',
                'bank' => 'nullable|string|max:200',
                'balance' => 'nullable|numeric',
                'status' => 'nullable|string',
            ]);
            $bankAccount->update(array_filter([
                'account_name' => $validated['name'] ?? null,
                'bank_name' => $validated['bank'] ?? null,
                'current_balance' => $validated['balance'] ?? null,
                'available_balance' => $validated['balance'] ?? null,
                'status' => $validated['status'] ?? null,
            ], fn ($v) => $v !== null));
            return response()->json(['record' => $bankAccount->fresh()]);
        });
    }

    public function deleteBankAccount(Request $request, BankAccount $bankAccount): JsonResponse
    {
        return $this->forTenant($request, function () use ($bankAccount): JsonResponse {
            $bankAccount->delete();
            return response()->json(['message' => 'Deleted']);
        });
    }

    public function createReconciliation(Request $request): JsonResponse
    {
        return $this->forTenant($request, function () use ($request): JsonResponse {
            $validated = $request->validate([
                'transaction_id' => 'required|exists:bank_transactions,id',
                'journal_entry_id' => 'nullable|exists:journal_entries,id',
            ]);
            $transaction = BankTransaction::query()->findOrFail($validated['transaction_id']);
            $transaction->update([
                'reconciliation_status' => 'reconciled',
                'journal_entry_id' => $validated['journal_entry_id'] ?? null,
            ]);
            return response()->json(['record' => $transaction->fresh()], 201);
        });
    }

    public function updateReconciliation(Request $request, BankTransaction $bankTransaction): JsonResponse
    {
        return $this->forTenant($request, function () use ($request, $bankTransaction): JsonResponse {
            $validated = $request->validate([
                'reconciliation_status' => 'nullable|string|in:reconciled,unreconciled',
                'journal_entry_id' => 'nullable|exists:journal_entries,id',
            ]);
            $bankTransaction->update(array_filter($validated, fn ($v) => $v !== null));
            return response()->json(['record' => $bankTransaction->fresh()]);
        });
    }

    /**
     * @template TReturn
     * @param callable(): TReturn $callback
     * @return TReturn
     */
    private function forTenant(Request $request, callable $callback): mixed
    {
        $tenantId = $request->user()?->tenant_id;

        if ($tenantId === null) {
            abort(401, 'Authentication required.');
        }

        TenantContext::set((int) $tenantId);

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

    private function currency(): string
    {
        static $currency = null;
        if ($currency === null) {
            $currency = CompanySetting::query()
                ->where('tenant_id', TenantContext::requireId())
                ->value('currency') ?? 'GHC';
        }
        return $currency;
    }

    private function money(float|int|string|null $amount): string
    {
        return $this->currency().' '.number_format((float) ($amount ?? 0), 2);
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
