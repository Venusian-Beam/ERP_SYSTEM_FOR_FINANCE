<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class PayablesController extends Controller
{
    public function vendors(Request $request): Response
    {
        $query = Supplier::query()
            ->withCount('invoices')
            ->withSum(['invoices as open_balance' => function ($q) {
                $q->whereIn('status', ['pending_approval', 'approved']);
            }], 'amount')
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->input('search') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return Inertia::render('payables/Vendors', [
            'vendors' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function vendorDetail(Request $request, string $id): Response
    {
        $vendor = Supplier::query()
            ->with(['invoices' => fn ($q) => $q->latest('invoice_date')->limit(10)])
            ->withSum(['invoices as total_billed'], 'amount')
            ->withSum(['invoices as open_balance' => fn ($q) => $q->whereIn('status', ['pending_approval', 'approved'])], 'amount')
            ->findOrFail($id);

        return Inertia::render('payables/VendorDetail', [
            'vendor' => $vendor,
        ]);
    }

    public function bills(Request $request): Response
    {
        $query = SupplierInvoice::query()
            ->with(['supplier:id,name', 'approvals'])
            ->latest('invoice_date');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('due_from')) {
            $query->where('due_date', '>=', $request->input('due_from'));
        }

        if ($request->filled('due_to')) {
            $query->where('due_date', '<=', $request->input('due_to'));
        }

        return Inertia::render('payables/Bills', [
            'bills'     => $query->paginate(20)->withQueryString(),
            'suppliers' => Supplier::query()->orderBy('name')->get(['id', 'name']),
            'filters'   => $request->only(['status', 'supplier_id', 'due_from', 'due_to']),
            'summary'   => [
                'total_pending' => SupplierInvoice::query()->where('status', 'pending_approval')->sum('amount'),
                'total_overdue' => SupplierInvoice::query()
                    ->whereIn('status', ['pending_approval', 'approved'])
                    ->where('due_date', '<', now()->toDateString())
                    ->sum('amount'),
                'total_approved' => SupplierInvoice::query()->where('status', 'approved')->sum('amount'),
            ],
        ]);
    }

    public function billDetail(Request $request, string $id): Response
    {
        $bill = SupplierInvoice::query()
            ->with(['supplier', 'approvals.approver:id,name'])
            ->findOrFail($id);

        return Inertia::render('payables/BillDetail', [
            'bill' => $bill,
        ]);
    }

    public function storeBill(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'supplier_id'              => ['required', 'integer', 'exists:suppliers,id'],
            'invoice_number'           => ['required', 'string', 'max:255'],
            'invoice_date'             => ['required', 'date'],
            'due_date'                 => ['required', 'date', 'after_or_equal:invoice_date'],
            'amount'                   => ['required', 'numeric', 'min:0.01'],
            'supporting_document_path' => ['nullable', 'string', 'max:2048'],
        ]);

        $bill = DB::transaction(function () use ($payload) {
            return SupplierInvoice::query()->create($payload);
        });

        return redirect()->route('payables.bills.show', $bill->id);
    }

    public function approveBill(Request $request, string $id): RedirectResponse
    {
        $payload = $request->validate([
            'status' => ['required', 'string', 'in:approved,rejected'],
            'notes'  => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($id, $payload) {
            SupplierInvoice::query()->findOrFail($id)->update(['status' => $payload['status']]);
        });

        return redirect()->route('payables.bills.show', $id);
    }

    public function payments(Request $request): Response
    {
        $payments = Payment::query()
            ->with(['customerInvoice.customer:id,name'])
            ->latest('paid_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('payables/Payments', [
            'payments' => $payments,
            'filters'  => $request->only(['from', 'to']),
        ]);
    }
}
