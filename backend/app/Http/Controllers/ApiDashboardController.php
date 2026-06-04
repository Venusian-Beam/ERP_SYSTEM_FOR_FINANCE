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

        // Calculate Totals
        $totalRevenue = (float) CustomerInvoice::sum('amount');
        $totalExpenses = (float) SupplierInvoice::sum('amount');
        
        // Calculate Receivables (unpaid amounts of all customer invoices)
        $totalReceivables = (float) CustomerInvoice::whereNotIn('status', ['paid'])
            ->sum(\Illuminate\Support\Facades\DB::raw('amount - paid_amount'));
            
        // Calculate Cash Position
        $totalCash = (float) \App\Models\BankAccount::sum('current_balance');

        // Lists for dashboard
        $invoices = CustomerInvoice::with('customer:id,name')
            ->whereIn('status', ['open', 'partial', 'overdue', 'issued'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        $bills = SupplierInvoice::with('supplier:id,name')
            ->whereIn('status', ['pending', 'open', 'pending_approval'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();
            
        // Calculate Monthly Trends for Current Year
        $currentYear = date('Y');
        $revenueTrend = array_fill(0, 12, 0);
        $expenseTrend = array_fill(0, 12, 0);
        
        $customerInvoicesThisYear = CustomerInvoice::whereYear('invoice_date', $currentYear)->get(['invoice_date', 'amount']);
        $supplierInvoicesThisYear = SupplierInvoice::whereYear('invoice_date', $currentYear)->get(['invoice_date', 'amount']);
        
        foreach ($customerInvoicesThisYear as $inv) {
            $monthIndex = (int) date('n', strtotime((string) $inv->invoice_date)) - 1;
            $revenueTrend[$monthIndex] += (float) $inv->amount;
        }
        
        foreach ($supplierInvoicesThisYear as $inv) {
            $monthIndex = (int) date('n', strtotime((string) $inv->invoice_date)) - 1;
            $expenseTrend[$monthIndex] += (float) $inv->amount;
        }

        if ($tenant) {
            TenantContext::clear();
        }

        return response()->json([
            'revenue' => $totalRevenue,
            'expenses' => $totalExpenses,
            'receivables' => $totalReceivables,
            'cash' => $totalCash,
            'active_invoices' => $invoices,
            'pending_bills' => $bills,
            'revenue_trend' => $revenueTrend,
            'expense_trend' => $expenseTrend,
        ]);
    }
}
