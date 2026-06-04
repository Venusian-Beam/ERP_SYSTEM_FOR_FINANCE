<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Services\PayablesService;
use App\Services\StructuredQueryBuilderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class AccountsPayableController extends Controller
{
    public function __construct(
        private readonly StructuredQueryBuilderService $filters,
        private readonly PayablesService $payables,
    ) {
    }

    public function index(Request $request): Response
    {
        $query = SupplierInvoice::query()->with(['supplier', 'approvals'])->latest('invoice_date');
        $this->filters->apply($query, $request->only(['status', 'supplier_id', 'due_from', 'due_to']), [
            'status' => 'status',
            'supplier_id' => 'supplier_id',
            'due_from' => 'due_date',
            'due_to' => 'due_date',
        ]);

        return Inertia::render('Finance/Payables/Index', [
            'invoices' => $query->paginate(20)->withQueryString(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'filters' => $request->only(['status', 'supplier_id', 'due_from', 'due_to']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'invoice_number' => ['required', 'string', 'max:255'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'supporting_document_path' => ['nullable', 'string', 'max:2048'],
            'approver_ids' => ['nullable', 'array'],
            'approver_ids.finance_manager' => ['nullable', 'integer'],
            'approver_ids.tenant_admin' => ['nullable', 'integer'],
            'approver_ids.auditor' => ['nullable', 'integer'],
        ]);

        $invoice = $this->payables->createInvoice($payload);

        return redirect()->route('accounts-payable.show', $invoice);
    }

    public function show(SupplierInvoice $accountsPayable): Response
    {
        return Inertia::render('Finance/Payables/Show', [
            'invoice' => $accountsPayable->load(['supplier', 'approvals']),
        ]);
    }

    public function update(Request $request, SupplierInvoice $accountsPayable): RedirectResponse
    {
        $payload = $request->validate([
            'status' => ['required', 'string', 'in:pending_approval,approved,rejected,paid'],
        ]);

        $accountsPayable->update($payload);

        return redirect()->route('accounts-payable.show', $accountsPayable);
    }
}
