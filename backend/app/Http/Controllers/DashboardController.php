<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\CustomerInvoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\SupplierInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        // Core financial KPIs — all scoped to active tenant via BelongsToTenant global scope
        $totalCashBalance = BankAccount::query()
            ->where('status', 'active')
            ->sum('current_balance');

        $totalPayablesDue = SupplierInvoice::query()
            ->whereIn('status', ['pending_approval', 'approved'])
            ->sum('amount');

        $totalReceivablesDue = CustomerInvoice::query()
            ->whereIn('status', ['issued', 'partially_paid'])
            ->sum('amount');

        $overduePayables = SupplierInvoice::query()
            ->whereIn('status', ['pending_approval', 'approved'])
            ->where('due_date', '<', now()->toDateString())
            ->sum('amount');

        $overdueReceivables = CustomerInvoice::query()
            ->whereIn('status', ['issued', 'partially_paid'])
            ->where('due_date', '<', now()->toDateString())
            ->sum('amount');

        // Month-to-date collections
        $mtdCollections = Payment::query()
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        // Pending approvals count (bills awaiting action)
        $pendingApprovals = SupplierInvoice::query()
            ->where('status', 'pending_approval')
            ->count();

        // Recent journal activity — eager loaded, no N+1
        $recentJournals = JournalEntry::query()
            ->with(['lines:id,journal_entry_id,debit,credit,financial_account_id'])
            ->latest('posted_at')
            ->limit(8)
            ->get(['id', 'reference', 'description', 'posted_at', 'status']);

        // Recent payables (bills due soonest)
        $upcomingBills = SupplierInvoice::query()
            ->with(['supplier:id,name'])
            ->whereIn('status', ['pending_approval', 'approved'])
            ->orderBy('due_date')
            ->limit(5)
            ->get(['id', 'invoice_number', 'supplier_id', 'amount', 'due_date', 'status']);

        // Recent receivables (invoices due soonest)
        $upcomingInvoices = CustomerInvoice::query()
            ->with(['customer:id,name'])
            ->whereIn('status', ['issued', 'partially_paid'])
            ->orderBy('due_date')
            ->limit(5)
            ->get(['id', 'invoice_number', 'customer_id', 'amount', 'due_date', 'status']);

        // Recent audit activity
        $recentActivity = AuditLog::query()
            ->with(['user:id,name'])
            ->latest()
            ->limit(10)
            ->get(['id', 'user_id', 'action', 'auditable_type', 'auditable_id', 'created_at']);

        return Inertia::render('Dashboard', [
            'kpis' => [
                'total_cash_balance'    => $totalCashBalance,
                'total_payables_due'    => $totalPayablesDue,
                'total_receivables_due' => $totalReceivablesDue,
                'overdue_payables'      => $overduePayables,
                'overdue_receivables'   => $overdueReceivables,
                'mtd_collections'       => $mtdCollections,
                'pending_approvals'     => $pendingApprovals,
            ],
            'recentJournals'    => $recentJournals,
            'upcomingBills'     => $upcomingBills,
            'upcomingInvoices'  => $upcomingInvoices,
            'recentActivity'    => $recentActivity,
        ]);
    }
}
