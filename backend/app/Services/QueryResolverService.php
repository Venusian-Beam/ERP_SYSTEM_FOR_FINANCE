<?php

declare(strict_types=1);

namespace App\Services;

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
use Illuminate\Database\Eloquent\Builder;

final class QueryResolverService
{
    /**
     * Resolve a parsed LLM intent into a safe, read-only platform answer.
     *
     * @return array{reply: string, intent: array<string, mixed>, facts: array<string, mixed>, suggestions: array<int, string>}
     */
    public function resolve(array $payload, string $question = ''): array
    {
        $questionLower = strtolower($question);
        $target = (string) ($payload['target'] ?? '');
        $filters = is_array($payload['filters'] ?? null) ? $payload['filters'] : [];

        if ($this->mentions($questionLower, ['payable', 'supplier invoice', 'vendor bill', 'bill']) || $target === 'supplier_invoices') {
            return $this->supplierInvoiceAnswer($filters, $payload);
        }

        if ($this->mentions($questionLower, ['receivable', 'customer invoice', 'invoice', 'collection']) || $target === 'customer_invoices') {
            return $this->customerInvoiceAnswer($filters, $payload);
        }

        if ($this->mentions($questionLower, ['vendor', 'supplier']) || $target === 'vendors') {
            return $this->vendorAnswer($payload);
        }

        if ($this->mentions($questionLower, ['customer', 'client']) || $target === 'customers') {
            return $this->customerAnswer($payload);
        }

        if ($this->mentions($questionLower, ['cash', 'bank', 'balance', 'runway', 'liquidity']) || in_array($target, ['bank_accounts', 'bank_transactions'], true)) {
            return $this->bankingAnswer($payload);
        }

        if ($this->mentions($questionLower, ['payment', 'receipt', 'paid', 'collected']) || $target === 'payments') {
            return $this->paymentAnswer($payload);
        }

        if ($this->mentions($questionLower, ['profit', 'loss', 'revenue', 'expense', 'income statement', 'balance sheet', 'cash flow', 'report', 'figures', 'financial statement'])) {
            return $this->reportsAnswer($payload);
        }

        if ($this->mentions($questionLower, ['audit', 'activity log', 'who changed'])) {
            return $this->auditAnswer($payload);
        }

        if ($this->mentions($questionLower, ['chart of accounts', 'account list', 'accounts']) || $target === 'financial_accounts') {
            return $this->accountsAnswer($payload);
        }

        if ($this->mentions($questionLower, ['ledger', 'journal', 'entry', 'transaction']) || in_array($target, ['journal_entries', 'journal_lines'], true)) {
            return $this->ledgerAnswer($payload);
        }

        if ($this->mentions($questionLower, ['company', 'settings', 'role', 'permission', 'user access'])) {
            return $this->settingsAnswer($payload);
        }

        return $this->overviewAnswer($payload);
    }

    private function supplierInvoiceAnswer(array $filters, array $payload): array
    {
        $query = SupplierInvoice::query()->with('supplier');
        $this->applyInvoiceFilters($query, $filters);

        $total = (float) (clone $query)->sum('amount');
        $count = (clone $query)->count();
        $pending = (float) SupplierInvoice::query()->whereIn('status', ['pending', 'pending_approval'])->sum('amount');
        $overdue = (float) SupplierInvoice::query()->whereDate('due_date', '<', now()->toDateString())->whereNotIn('status', ['paid', 'rejected'])->sum('amount');
        $latest = (clone $query)->orderByDesc('due_date')->take(5)->get();

        $lines = $latest->map(fn (SupplierInvoice $invoice): string =>
            "{$invoice->invoice_number} from " . ($invoice->supplier?->name ?? 'Unknown supplier') .
            " is {$invoice->status}, due {$invoice->due_date?->toDateString()}, amount " . $this->money((float) $invoice->amount)
        )->all();

        return $this->answer(
            $payload,
            "I found {$count} payable record(s) totaling {$this->money($total)}. Pending payables are {$this->money($pending)}, and overdue payables total {$this->money($overdue)}." . $this->lines($lines),
            ['Show overdue bills', 'List pending payables', 'Summarize vendors with balances'],
            compact('count', 'total', 'pending', 'overdue')
        );
    }

    private function customerInvoiceAnswer(array $filters, array $payload): array
    {
        $query = CustomerInvoice::query()->with('customer');
        $this->applyInvoiceFilters($query, $filters);

        $total = (float) (clone $query)->sum('amount');
        $paid = (float) (clone $query)->sum('paid_amount');
        $outstanding = $total - $paid;
        $count = (clone $query)->count();
        $overdue = (float) CustomerInvoice::query()
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['paid'])
            ->get()
            ->sum(fn (CustomerInvoice $invoice): float => (float) $invoice->amount - (float) $invoice->paid_amount);
        $latest = (clone $query)->orderByDesc('due_date')->take(5)->get();

        $lines = $latest->map(fn (CustomerInvoice $invoice): string =>
            "{$invoice->invoice_number} for " . ($invoice->customer?->name ?? 'Unknown customer') .
            " is {$invoice->status}, outstanding " . $this->money((float) $invoice->amount - (float) $invoice->paid_amount)
        )->all();

        return $this->answer(
            $payload,
            "I found {$count} receivable invoice(s) totaling {$this->money($total)}. Collections recorded are {$this->money($paid)}, leaving {$this->money($outstanding)} outstanding. Overdue receivables total {$this->money($overdue)}." . $this->lines($lines),
            ['Show overdue invoices', 'List customer balances', 'Summarize receipts'],
            compact('count', 'total', 'paid', 'outstanding', 'overdue')
        );
    }

    private function bankingAnswer(array $payload): array
    {
        $cash = (float) BankAccount::query()->where('status', 'active')->sum('current_balance');
        $available = (float) BankAccount::query()->where('status', 'active')->sum('available_balance');
        $accounts = BankAccount::query()->where('status', 'active')->orderByDesc('current_balance')->take(5)->get();
        $unreconciledCount = BankTransaction::query()->where('reconciliation_status', 'unreconciled')->count();
        $unreconciledAmount = (float) BankTransaction::query()->where('reconciliation_status', 'unreconciled')->sum('amount');

        $lines = $accounts->map(fn (BankAccount $account): string =>
            "{$account->account_name} at {$account->bank_name}: {$this->money((float) $account->current_balance)} current, {$this->money((float) $account->available_balance)} available"
        )->all();

        return $this->answer(
            $payload,
            "Current cash across active bank accounts is {$this->money($cash)}. Available cash is {$this->money($available)}. There are {$unreconciledCount} unreconciled bank transaction(s) with a net value of {$this->money($unreconciledAmount)}." . $this->lines($lines),
            ['Show bank transactions', 'Review unreconciled items', 'Forecast cash position'],
            compact('cash', 'available', 'unreconciledCount', 'unreconciledAmount')
        );
    }

    private function vendorAnswer(array $payload): array
    {
        $vendors = Supplier::query()
            ->withSum(['invoices as open_balance' => fn (Builder $query) => $query->whereNotIn('status', ['paid', 'rejected'])], 'amount')
            ->orderBy('name')
            ->take(8)
            ->get();
        $count = Supplier::query()->count();
        $active = Supplier::query()->where('status', 'active')->count();
        $balance = (float) SupplierInvoice::query()->whereNotIn('status', ['paid', 'rejected'])->sum('amount');

        $lines = $vendors->map(fn (Supplier $vendor): string =>
            "{$vendor->name}: {$vendor->status}, open balance {$this->money((float) ($vendor->open_balance ?? 0))}"
        )->all();

        return $this->answer($payload, "There are {$count} vendors, {$active} active, with open supplier invoice balances totaling {$this->money($balance)}." . $this->lines($lines), ['Show pending vendor bills', 'Find active vendors'], compact('count', 'active', 'balance'));
    }

    private function customerAnswer(array $payload): array
    {
        $customers = Customer::query()
            ->withSum(['invoices as invoice_total'], 'amount')
            ->withSum(['invoices as paid_total'], 'paid_amount')
            ->orderBy('name')
            ->take(8)
            ->get();
        $count = Customer::query()->count();
        $active = Customer::query()->where('status', 'active')->count();
        $ar = (float) CustomerInvoice::query()->get()->sum(fn (CustomerInvoice $invoice): float => (float) $invoice->amount - (float) $invoice->paid_amount);

        $lines = $customers->map(function (Customer $customer): string {
            $outstanding = (float) ($customer->invoice_total ?? 0) - (float) ($customer->paid_total ?? 0);
            return "{$customer->name}: {$customer->status}, outstanding {$this->money($outstanding)}";
        })->all();

        return $this->answer($payload, "There are {$count} customers, {$active} active, with total outstanding receivables of {$this->money($ar)}." . $this->lines($lines), ['Show overdue customers', 'List open invoices'], compact('count', 'active', 'ar'));
    }

    private function paymentAnswer(array $payload): array
    {
        $total = (float) Payment::query()->sum('amount');
        $count = Payment::query()->count();
        $latest = Payment::query()->with('invoice.customer')->latest('paid_at')->take(5)->get();
        $lines = $latest->map(fn (Payment $payment): string =>
            "{$payment->reference}: {$this->money((float) $payment->amount)} by {$payment->method} on {$payment->paid_at?->toDateString()} for " . ($payment->invoice?->customer?->name ?? 'customer invoice')
        )->all();

        return $this->answer($payload, "Recorded customer receipts total {$this->money($total)} across {$count} payment(s)." . $this->lines($lines), ['Show unmatched receipts', 'List customer invoices'], compact('total', 'count'));
    }

    private function ledgerAnswer(array $payload): array
    {
        $entries = JournalEntry::query()->withCount('lines')->latest('posted_at')->take(5)->get();
        $entryCount = JournalEntry::query()->count();
        $debits = (float) JournalLine::query()->sum('debit');
        $credits = (float) JournalLine::query()->sum('credit');
        $lines = $entries->map(fn (JournalEntry $entry): string =>
            "{$entry->reference}: {$entry->status}, {$entry->lines_count} line(s), posted {$entry->posted_at?->toDateString()} - {$entry->description}"
        )->all();

        return $this->answer($payload, "The general ledger has {$entryCount} journal entrie(s). Posted journal lines total {$this->money($debits)} debits and {$this->money($credits)} credits." . $this->lines($lines), ['Show latest journal entries', 'Summarize chart of accounts'], compact('entryCount', 'debits', 'credits'));
    }

    private function accountsAnswer(array $payload): array
    {
        $accounts = FinancialAccount::query()
            ->withSum(['journalLines as debit_total'], 'debit')
            ->withSum(['journalLines as credit_total'], 'credit')
            ->orderBy('code')
            ->get();
        $active = $accounts->where('is_active', true)->count();
        $byType = $accounts->groupBy('type')->map->count()->all();
        $lines = $accounts->take(8)->map(fn (FinancialAccount $account): string =>
            "{$account->code} {$account->name}: {$account->type}, normal {$account->normal_balance}, " . ($account->is_active ? 'active' : 'inactive')
        )->all();

        return $this->answer($payload, "The chart of accounts has {$accounts->count()} accounts, with {$active} active. Breakdown: " . $this->keyValue($byType) . '.' . $this->lines($lines), ['Show general ledger', 'Summarize balance sheet'], ['count' => $accounts->count(), 'active' => $active, 'byType' => $byType]);
    }

    private function reportsAnswer(array $payload): array
    {
        $revenue = abs($this->accountTypeNet('revenue'));
        $expenses = abs($this->accountTypeNet('expense'));
        $assets = abs($this->accountTypeNet('asset'));
        $liabilities = abs($this->accountTypeNet('liability'));
        $equity = abs($this->accountTypeNet('equity'));
        $netIncome = $revenue - $expenses;

        if ($revenue === 0.0 && $expenses === 0.0) {
            $revenue = (float) CustomerInvoice::query()->sum('amount');
            $expenses = (float) SupplierInvoice::query()->sum('amount');
            $netIncome = $revenue - $expenses;
        }

        return $this->answer($payload, "Financial summary: revenue is {$this->money($revenue)}, expenses are {$this->money($expenses)}, and net income is {$this->money($netIncome)}. Balance sheet snapshot: assets {$this->money($assets)}, liabilities {$this->money($liabilities)}, equity {$this->money($equity)}.", ['Show profit and loss', 'Show balance sheet', 'Show cash position'], compact('revenue', 'expenses', 'netIncome', 'assets', 'liabilities', 'equity'));
    }

    private function auditAnswer(array $payload): array
    {
        $count = AuditLog::query()->count();
        $latest = AuditLog::query()->with('user:id,name')->latest()->take(5)->get();
        $lines = $latest->map(fn (AuditLog $log): string =>
            "{$log->created_at?->toDateTimeString()}: {$log->action} {$log->auditable_type} #{$log->auditable_id} by " . ($log->user?->name ?? 'system')
        )->all();

        return $this->answer($payload, "The audit trail contains {$count} logged event(s)." . $this->lines($lines), ['Show recent changes', 'Review approvals'], compact('count'));
    }

    private function settingsAnswer(array $payload): array
    {
        $company = CompanySetting::query()->first();
        $roles = Role::query()->withCount('users')->orderBy('name')->take(8)->get();
        $lines = $roles->map(fn (Role $role): string => "{$role->name}: {$role->users_count} user(s)")->all();
        $companyName = $company?->company_name ?? 'No company settings record found';
        $currency = $company?->currency ?? 'not set';

        return $this->answer($payload, "Company settings show {$companyName}, base currency {$currency}. There are {$roles->count()} role record(s) available." . $this->lines($lines), ['Show users', 'Show roles', 'Show company settings'], ['company' => $companyName, 'currency' => $currency, 'roles' => $roles->count()]);
    }

    private function overviewAnswer(array $payload): array
    {
        $ar = (float) CustomerInvoice::query()->get()->sum(fn (CustomerInvoice $invoice): float => (float) $invoice->amount - (float) $invoice->paid_amount);
        $ap = (float) SupplierInvoice::query()->whereNotIn('status', ['paid', 'rejected'])->sum('amount');
        $cash = (float) BankAccount::query()->where('status', 'active')->sum('current_balance');
        $customers = Customer::query()->count();
        $vendors = Supplier::query()->count();

        return $this->answer($payload, "Here is the current finance snapshot: cash {$this->money($cash)}, receivables outstanding {$this->money($ar)}, open payables {$this->money($ap)}, {$customers} customer(s), and {$vendors} vendor(s). Ask me for any detail and I can drill into it.", ['Show pending payables', 'Show overdue receivables', 'Show cash balances'], compact('cash', 'ar', 'ap', 'customers', 'vendors'));
    }

    private function applyInvoiceFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $column => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if ($column === 'invoice_number') {
                $query->where('invoice_number', 'ilike', '%' . (string) $value . '%');
            }

            if ($column === 'status') {
                $query->where('status', (string) $value);
            }

            if ($column === 'amount') {
                $query->where('amount', (float) $value);
            }
        }
    }

    private function accountTypeNet(string $type): float
    {
        return (float) JournalLine::query()
            ->join('financial_accounts', 'journal_lines.financial_account_id', '=', 'financial_accounts.id')
            ->where('financial_accounts.type', $type)
            ->selectRaw('COALESCE(SUM(journal_lines.credit - journal_lines.debit), 0) as net')
            ->value('net');
    }

    private function answer(array $payload, string $reply, array $suggestions, array $facts = []): array
    {
        return [
            'reply' => $reply,
            'intent' => $payload,
            'facts' => $facts,
            'suggestions' => $suggestions,
        ];
    }

    private function mentions(string $question, array $terms): bool
    {
        foreach ($terms as $term) {
            if (str_contains($question, $term)) {
                return true;
            }
        }

        return false;
    }

    private function money(float $amount): string
    {
        return 'GHC ' . number_format($amount, 2);
    }

    /**
     * @param array<int, string> $lines
     */
    private function lines(array $lines): string
    {
        if ($lines === []) {
            return '';
        }

        return "\n\n" . implode("\n", array_map(fn (string $line): string => '- ' . $line, $lines));
    }

    /**
     * @param array<string, int> $values
     */
    private function keyValue(array $values): string
    {
        return implode(', ', array_map(
            fn (string $key, int $value): string => "{$key}: {$value}",
            array_keys($values),
            $values,
        ));
    }
}
