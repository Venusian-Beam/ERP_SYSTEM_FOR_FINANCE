<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class ReceivablesController extends Controller
{
    public function customers(Request $request): Response
    {
        $query = Customer::query()
            ->withCount('invoices')
            ->withSum(['invoices as total_billed'], 'amount')
            ->withSum(['invoices as outstanding_balance' => fn ($q) => $q->whereIn('status', ['issued', 'partially_paid'])], 'amount')
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->input('search') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return Inertia::render('receivables/Customers', [
            'customers' => $query->paginate(20)->withQueryString(),
            'filters'   => $request->only(['search', 'status']),
        ]);
    }

    public function customerDetail(Request $request, string $id): Response
    {
        $customer = Customer::query()
            ->with(['invoices' => fn ($q) => $q->latest('invoice_date')->limit(10)->with('payments')])
            ->withSum(['invoices as total_billed'], 'amount')
            ->withSum(['invoices as outstanding_balance' => fn ($q) => $q->whereIn('status', ['issued', 'partially_paid'])], 'amount')
            ->withSum(['invoices as total_collected' => fn ($q) => $q->where('status', 'paid')], 'paid_amount')
            ->findOrFail($id);

        return Inertia::render('receivables/CustomerDetail', [
            'customer' => $customer,
        ]);
    }

    public function invoices(Request $request): Response
    {
        $query = CustomerInvoice::query()
            ->with(['customer:id,name'])
            ->latest('invoice_date');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('due_from')) {
            $query->where('due_date', '>=', $request->input('due_from'));
        }

        if ($request->filled('due_to')) {
            $query->where('due_date', '<=', $request->input('due_to'));
        }

        return Inertia::render('receivables/Invoices', [
            'invoices'  => $query->paginate(20)->withQueryString(),
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'filters'   => $request->only(['status', 'customer_id', 'due_from', 'due_to']),
            'summary'   => [
                'total_outstanding' => CustomerInvoice::query()
                    ->whereIn('status', ['issued', 'partially_paid'])
                    ->sum('amount'),
                'total_overdue' => CustomerInvoice::query()
                    ->whereIn('status', ['issued', 'partially_paid'])
                    ->where('due_date', '<', now()->toDateString())
                    ->sum('amount'),
                'total_collected_mtd' => Payment::query()
                    ->whereMonth('paid_at', now()->month)
                    ->sum('amount'),
            ],
        ]);
    }

    public function invoiceDetail(Request $request, string $id): Response
    {
        $invoice = CustomerInvoice::query()
            ->with(['customer', 'payments', 'notes'])
            ->findOrFail($id);

        return Inertia::render('receivables/InvoiceDetail', [
            'invoice' => $invoice,
        ]);
    }

    public function storeInvoice(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'customer_id'    => ['required', 'integer', 'exists:customers,id'],
            'invoice_number' => ['required', 'string', 'max:255'],
            'invoice_date'   => ['required', 'date'],
            'due_date'       => ['required', 'date', 'after_or_equal:invoice_date'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
        ]);

        $invoice = DB::transaction(function () use ($payload) {
            return CustomerInvoice::query()->create($payload);
        });

        return redirect()->route('receivables.invoices.show', $invoice->id);
    }

    public function receipts(Request $request): Response
    {
        $query = Payment::query()
            ->with(['invoice.customer:id,name'])
            ->latest('paid_at');

        if ($request->filled('from')) {
            $query->where('paid_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('paid_at', '<=', $request->input('to'));
        }

        return Inertia::render('receivables/Receipts', [
            'receipts' => $query->paginate(20)->withQueryString(),
            'filters'  => $request->only(['from', 'to']),
        ]);
    }
}
