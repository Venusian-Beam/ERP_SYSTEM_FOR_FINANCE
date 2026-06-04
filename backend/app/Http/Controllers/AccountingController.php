<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class AccountingController extends Controller
{
    public function chartOfAccounts(Request $request): Response
    {
        $accounts = FinancialAccount::query()
            ->withSum(['journalLines as debit_total'], 'debit')
            ->withSum(['journalLines as credit_total'], 'credit')
            ->orderBy('code')
            ->get();

        // Group by type for the hierarchical view
        $grouped = $accounts->groupBy('type')->map(fn ($group) => $group->values());

        return Inertia::render('accounting/ChartOfAccounts', [
            'accounts' => $accounts,
            'grouped'  => $grouped,
        ]);
    }

    public function journalEntries(Request $request): Response
    {
        $query = JournalEntry::query()
            ->withCount('lines')
            ->latest('posted_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from')) {
            $query->where('posted_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('posted_at', '<=', $request->input('to'));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference', 'ilike', '%' . $request->input('search') . '%')
                  ->orWhere('description', 'ilike', '%' . $request->input('search') . '%');
            });
        }

        return Inertia::render('accounting/JournalEntries', [
            'entries' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['status', 'from', 'to', 'search']),
        ]);
    }

    public function journalEntryDetail(Request $request, string $id): Response
    {
        $entry = JournalEntry::query()
            ->with(['lines.account:id,code,name,type'])
            ->findOrFail($id);

        // Verify entry is balanced
        $totalDebits  = $entry->lines->sum('debit');
        $totalCredits = $entry->lines->sum('credit');

        return Inertia::render('accounting/JournalEntryDetail', [
            'entry'        => $entry,
            'totalDebits'  => $totalDebits,
            'totalCredits' => $totalCredits,
            'isBalanced'   => round($totalDebits, 2) === round($totalCredits, 2),
        ]);
    }

    public function storeJournalEntry(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'reference'   => ['required', 'string', 'max:255'],
            'posted_at'   => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'lines'       => ['required', 'array', 'min:2'],
            'lines.*.financial_account_id' => ['required', 'integer', 'exists:financial_accounts,id'],
            'lines.*.debit'  => ['required', 'numeric', 'min:0'],
            'lines.*.credit' => ['required', 'numeric', 'min:0'],
            'lines.*.memo'   => ['nullable', 'string'],
        ]);

        $entry = DB::transaction(function () use ($payload) {
            $entry = JournalEntry::query()->create([
                'reference'   => $payload['reference'],
                'posted_at'   => $payload['posted_at'],
                'description' => $payload['description'] ?? null,
                'status'      => 'posted',
                'created_by'  => auth()->id(),
            ]);

            foreach ($payload['lines'] as $line) {
                $entry->lines()->create($line);
            }

            // Validate the entry is balanced before committing
            $debits  = array_sum(array_column($payload['lines'], 'debit'));
            $credits = array_sum(array_column($payload['lines'], 'credit'));

            if (round($debits, 2) !== round($credits, 2)) {
                throw new \RuntimeException('Journal entry is not balanced. Debits must equal Credits.');
            }

            return $entry;
        });

        return redirect()->route('accounting.journals.show', $entry->id);
    }

    public function generalLedger(Request $request): Response
    {
        $from      = $request->input('from', now()->startOfMonth()->toDateString());
        $to        = $request->input('to', now()->toDateString());
        $accountId = $request->input('account_id');

        $query = JournalLine::query()
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('financial_accounts', 'journal_lines.financial_account_id', '=', 'financial_accounts.id')
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.posted_at', [$from, $to])
            ->select(
                'journal_lines.id',
                'journal_entries.reference',
                'journal_entries.posted_at',
                'journal_entries.description',
                'financial_accounts.code as account_code',
                'financial_accounts.name as account_name',
                'journal_lines.debit',
                'journal_lines.credit',
                'journal_lines.memo',
            )
            ->orderBy('journal_entries.posted_at', 'desc');

        if ($accountId) {
            $query->where('journal_lines.financial_account_id', $accountId);
        }

        return Inertia::render('accounting/GeneralLedger', [
            'ledger'   => $query->paginate(50)->withQueryString(),
            'accounts' => FinancialAccount::query()->orderBy('code')->get(['id', 'code', 'name']),
            'filters'  => $request->only(['from', 'to', 'account_id']),
        ]);
    }
}
