<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerInvoice;
use App\Models\Customer;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        // Load the related customer name
        $invoices = CustomerInvoice::with('customer')->latest()->get();
        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer', // Usually exists:id,customer
            'invoice_number' => 'required|string|max:50',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'amount' => 'required|numeric',
            'status' => 'required|string',
        ]);

        $validated['tenant_id'] = 1;
        $validated['paid_amount'] = 0;
        $validated['is_finalized'] = false;

        // Ensure customer exists or create a dummy one for the demo
        if (!Customer::find($validated['customer_id'])) {
            $customer = Customer::create(['tenant_id' => 1, 'name' => 'Demo Customer ' . rand(1,100)]);
            $validated['customer_id'] = $customer->id;
        }

        $invoice = CustomerInvoice::create($validated);
        $invoice->load('customer');

        return response()->json($invoice, 201);
    }

    public function destroy(CustomerInvoice $invoice)
    {
        $invoice->delete();
        return response()->json(['message' => 'Invoice permanently deleted']);
    }
}
