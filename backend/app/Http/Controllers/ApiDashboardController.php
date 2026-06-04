<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ApiDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Set tenant context to demo for API testing
        $tenant = Tenant::query()->where('slug', 'demo')->first();
        if ($tenant) {
            TenantContext::set((int) $tenant->id);
        }

        $totalRevenue = 475896.00; 
        $totalExpenses = 271452.00;

        $invoices = CustomerInvoice::with('customer:id,name')
            ->whereIn('status', ['open', 'partial', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        $bills = SupplierInvoice::with('supplier:id,name')
            ->whereIn('status', ['pending', 'open'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        if ($tenant) {
            TenantContext::clear();
        }

        return response()->json([
            'revenue' => $totalRevenue,
            'expenses' => $totalExpenses,
            'active_invoices' => $invoices,
            'pending_bills' => $bills,
        ]);
    }
}
