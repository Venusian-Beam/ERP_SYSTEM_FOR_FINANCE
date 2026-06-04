<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Reconciliation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class TreasuryController extends Controller
{
    public function bankAccounts(Request $request): Response
    {
        $accounts = BankAccount::query()
            ->withCount('transactions')
            ->orderBy('bank_name')
            ->get();

        return Inertia::render('treasury/BankAccounts', [
            'accounts' => $accounts,
        ]);
    }

    public function reconciliation(Request $request): Response
    {
        $accountId = $request->input('bank_account_id');

        $accounts = BankAccount::query()->orderBy('bank_name')->get(['id', 'account_name', 'bank_name']);

        $unmatchedTransactions = $accountId
            ? BankTransaction::query()
                ->where('bank_account_id', $accountId)
                ->where('reconciliation_status', 'unreconciled')
                ->orderBy('transaction_date', 'desc')
                ->get()
            : collect();

        return Inertia::render('treasury/Reconciliation', [
            'accounts'               => $accounts,
            'selectedAccountId'      => $accountId,
            'unmatchedTransactions'  => $unmatchedTransactions,
        ]);
    }

    public function cashForecast(Request $request): Response
    {
        // Deterministic: Show only current balances and known open payables/receivables
        $totalBankBalance = BankAccount::query()->where('status', 'active')->sum('current_balance');

        $openPayables = \App\Models\SupplierInvoice::query()
            ->whereIn('status', ['pending_approval', 'approved'])
            ->sum('amount');

        $openReceivables = \App\Models\CustomerInvoice::query()
            ->whereIn('status', ['issued', 'partially_paid'])
            ->sum('amount');

        return Inertia::render('treasury/CashForecast', [
            'totalBankBalance' => $totalBankBalance,
            'openPayables'     => $openPayables,
            'openReceivables'  => $openReceivables,
        ]);
    }
}
