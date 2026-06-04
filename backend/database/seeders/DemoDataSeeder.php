<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\Tenant;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

final class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'demo')->first();
        if (!$tenant) return;
        
        TenantContext::set((int) $tenant->id);

        // Customers
        $acme = Customer::query()->firstOrCreate(['name' => 'Acme Corp', 'tenant_id' => $tenant->id], ['email' => 'billing@acme.com', 'status' => 'active']);
        $globex = Customer::query()->firstOrCreate(['name' => 'Globex Industries', 'tenant_id' => $tenant->id], ['email' => 'accounts@globex.com', 'status' => 'active']);
        $stark = Customer::query()->firstOrCreate(['name' => 'Stark Industries', 'tenant_id' => $tenant->id], ['email' => 'tony@stark.com', 'status' => 'active']);

        // Invoices
        CustomerInvoice::query()->firstOrCreate(['invoice_number' => 'INV-2841'], [
            'tenant_id' => $tenant->id,
            'customer_id' => $acme->id,
            'invoice_date' => Carbon::now()->subDays(10),
            'due_date' => Carbon::now()->addDays(5),
            'amount' => 48200.00,
            'paid_amount' => 43380.00, // 90% paid
            'status' => 'partial',
            'is_finalized' => true,
        ]);

        CustomerInvoice::query()->firstOrCreate(['invoice_number' => 'INV-2840'], [
            'tenant_id' => $tenant->id,
            'customer_id' => $globex->id,
            'invoice_date' => Carbon::now()->subDays(45),
            'due_date' => Carbon::now()->subDays(14),
            'amount' => 18750.00,
            'paid_amount' => 8437.50, // 45% paid
            'status' => 'overdue',
            'is_finalized' => true,
        ]);

        CustomerInvoice::query()->firstOrCreate(['invoice_number' => 'INV-2839'], [
            'tenant_id' => $tenant->id,
            'customer_id' => $stark->id,
            'invoice_date' => Carbon::now()->subDays(18),
            'due_date' => Carbon::now()->addDays(12),
            'amount' => 12840.00,
            'paid_amount' => 8346.00, // 65% paid
            'status' => 'partial',
            'is_finalized' => true,
        ]);

        // Suppliers
        $vendor1 = Supplier::query()->firstOrCreate(['name' => 'Cloud Hosting Inc.', 'tenant_id' => $tenant->id], ['email' => 'billing@cloud.com', 'status' => 'active']);
        
        // Bills
        SupplierInvoice::query()->firstOrCreate(['invoice_number' => 'BILL-9001'], [
            'tenant_id' => $tenant->id,
            'supplier_id' => $vendor1->id,
            'invoice_date' => Carbon::now()->subDays(5),
            'due_date' => Carbon::now()->addDays(20),
            'amount' => 12450.00,
            'status' => 'pending',
            'is_finalized' => true,
        ]);

        TenantContext::clear();
    }
}
