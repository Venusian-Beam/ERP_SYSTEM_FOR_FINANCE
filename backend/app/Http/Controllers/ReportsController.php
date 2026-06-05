<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class ReportsController extends Controller
{
    public function profitLoss(Request $request): Response
    {
        $from = $request->input('from', now()->startOfYear()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        // Revenue: All journal lines posted to income accounts in the period
        $revenue = JournalLine::query()
            ->join('financial_accounts', 'journal_lines.financial_account_id', '=', 'financial_accounts.id')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('financial_accounts.type', 'revenue')
            ->whereBetween('journal_entries.posted_at', [$from, $to])
            ->where('journal_entries.status', 'posted')
            ->select(
                'financial_accounts.code',
                'financial_accounts.name',
                DB::raw('SUM(journal_lines.credit) - SUM(journal_lines.debit) as net_amount')
            )
            ->groupBy('financial_accounts.id', 'financial_accounts.code', 'financial_accounts.name')
            ->orderBy('financial_accounts.code')
            ->get();

        // Expenses: All journal lines posted to expense accounts in the period
        $expenses = JournalLine::query()
            ->join('financial_accounts', 'journal_lines.financial_account_id', '=', 'financial_accounts.id')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('financial_accounts.type', 'expense')
            ->whereBetween('journal_entries.posted_at', [$from, $to])
            ->where('journal_entries.status', 'posted')
            ->select(
                'financial_accounts.code',
                'financial_accounts.name',
                DB::raw('SUM(journal_lines.debit) - SUM(journal_lines.credit) as net_amount')
            )
            ->groupBy('financial_accounts.id', 'financial_accounts.code', 'financial_accounts.name')
            ->orderBy('financial_accounts.code')
            ->get();

        return Inertia::render('reports/ProfitLoss', [
            'revenue'  => $revenue,
            'expenses' => $expenses,
            'filters'  => compact('from', 'to'),
        ]);
    }

    public function balanceSheet(Request $request): Response
    {
        $asOf = $request->input('as_of', now()->toDateString());

        $ledgerLines = JournalLine::query()
            ->join('financial_accounts', 'journal_lines.financial_account_id', '=', 'financial_accounts.id')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->whereIn('financial_accounts.type', ['asset', 'liability', 'equity'])
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.posted_at', '<=', $asOf)
            ->select(
                'financial_accounts.type',
                'financial_accounts.code',
                'financial_accounts.name',
                DB::raw('CASE WHEN financial_accounts.type = \'asset\' THEN SUM(journal_lines.debit) - SUM(journal_lines.credit) ELSE SUM(journal_lines.credit) - SUM(journal_lines.debit) END as balance')
            )
            ->groupBy('financial_accounts.id', 'financial_accounts.type', 'financial_accounts.code', 'financial_accounts.name')
            ->orderBy('financial_accounts.type')
            ->orderBy('financial_accounts.code')
            ->get();

        return Inertia::render('reports/BalanceSheet', [
            'assets'      => $ledgerLines->where('type', 'asset')->values(),
            'liabilities' => $ledgerLines->where('type', 'liability')->values(),
            'equity'      => $ledgerLines->where('type', 'equity')->values(),
            'asOf'        => $asOf,
        ]);
    }

    public function cashFlow(Request $request): Response
    {
        // Cash flow derived from bank transactions
        $from = $request->input('from', now()->startOfYear()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $inflows = \App\Models\BankTransaction::query()
            ->where('type', 'credit')
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');

        $outflows = \App\Models\BankTransaction::query()
            ->where('type', 'debit')
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');

        return Inertia::render('reports/CashFlowStatement', [
            'inflows' => $inflows,
            'outflows' => $outflows,
            'netFlow' => $inflows - $outflows,
            'filters' => compact('from', 'to'),
        ]);
    }

    public function auditTrail(Request $request): Response
    {
        $query = AuditLog::query()
            ->with('user:id,name')
            ->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('model')) {
            $query->where('auditable_type', 'like', '%' . $request->input('model') . '%');
        }

        return Inertia::render('reports/AuditTrail', [
            'logs'    => $query->paginate(50)->withQueryString(),
            'filters' => $request->only(['action', 'model']),
        ]);
    }
}
