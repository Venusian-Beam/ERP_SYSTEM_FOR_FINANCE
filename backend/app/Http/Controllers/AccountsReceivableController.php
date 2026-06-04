<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Services\InvoiceSequenceService;
use App\Services\ReceivablesService;
use App\Services\StructuredQueryBuilderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class AccountsReceivableController extends Controller
{
    public function __construct(
        private readonly StructuredQueryBuilderService $filters,
        private readonly ReceivablesService $receivables,
        private readonly InvoiceSequenceService $sequences,
    ) {
    }

    public function index(Request $request): Response
    {
        $query = CustomerInvoice::query()->with(['customer', 'payments'])->latest('invoice_date');
        $this->filters->apply($query, $request->only(['status', 'customer_id', 'due_from', 'due_to']), [
            'status' => 'status',
            'customer_id' => 'customer_id',
            'due_from' => 'due_date',
            'due_to' => 'due_date',
        ]);

        return Inertia::render('Finance/Receivables/Index', [
            'invoices' => $query->paginate(20)->withQueryString(),
            'customers' => Customer::query()->orderBy('name')->get(),
            'filters' => $request->only(['status', 'customer_id', 'due_from', 'due_to']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $invoice = $this->sequences->insideSequenceLock(function () use ($payload): CustomerInvoice {
            return CustomerInvoice::query()->create($payload + [
                'invoice_number' => $payload['invoice_number'] ?? $this->sequences->nextCustomerInvoiceNumber(),
                'paid_amount' => 0,
                'status' => 'issued',
                'is_finalized' => true,
            ]);
        });

        return redirect()->route('accounts-receivable.show', $invoice);
    }

    public function show(CustomerInvoice $accountsReceivable): Response
    {
        return Inertia::render('Finance/Receivables/Show', [
            'invoice' => $accountsReceivable->load(['customer', 'payments']),
        ]);
    }

    public function update(Request $request, CustomerInvoice $accountsReceivable): RedirectResponse
    {
        $payload = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'reference' => ['required', 'string', 'max:255'],
            'method' => ['nullable', 'string', 'max:255'],
        ]);

        $this->receivables->recordPayment($accountsReceivable, $payload);

        return redirect()->route('accounts-receivable.show', $accountsReceivable);
    }
}
