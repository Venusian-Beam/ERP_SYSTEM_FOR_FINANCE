<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\JournalEntry;
use App\Services\LedgerPostingService;
use App\Services\StructuredQueryBuilderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class LedgerController extends Controller
{
    public function __construct(
        private readonly StructuredQueryBuilderService $filters,
        private readonly LedgerPostingService $posting,
    ) {
    }

    public function index(Request $request): Response
    {
        $query = JournalEntry::query()->with('lines.account')->latest('posted_at');
        $this->filters->apply($query, $request->only(['status', 'posted_from', 'posted_to']), [
            'status' => 'status',
            'posted_from' => 'posted_at',
            'posted_to' => 'posted_at',
        ]);

        return Inertia::render('Finance/Ledger/Index', [
            'entries' => $query->paginate(20)->withQueryString(),
            'accounts' => FinancialAccount::query()->where('is_active', true)->orderBy('code')->get(),
            'filters' => $request->only(['status', 'posted_from', 'posted_to']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'reference' => ['required', 'string', 'max:255'],
            'posted_at' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.financial_account_id' => ['required', 'integer', 'exists:financial_accounts,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.memo' => ['nullable', 'string', 'max:255'],
        ]);

        $payload['created_by'] = $request->user()?->id;
        $entry = $this->posting->post($payload);

        return redirect()->route('ledger.show', $entry);
    }

    public function show(JournalEntry $ledger): Response
    {
        return Inertia::render('Finance/Ledger/Show', [
            'entry' => $ledger->load('lines.account'),
        ]);
    }
}
