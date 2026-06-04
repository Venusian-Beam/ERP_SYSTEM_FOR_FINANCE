<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

// Accounting
Route::prefix('accounting')->group(function () {
    Route::get('chart-of-accounts', [\App\Http\Controllers\AccountingController::class, 'chartOfAccounts'])->name('accounting.coa');
    Route::get('journal-entries', [\App\Http\Controllers\AccountingController::class, 'journalEntries'])->name('accounting.journals');
    Route::post('journal-entries', [\App\Http\Controllers\AccountingController::class, 'storeJournalEntry'])->name('accounting.journals.store');
    Route::get('journal-entries/{id}', [\App\Http\Controllers\AccountingController::class, 'journalEntryDetail'])->name('accounting.journals.show');
    Route::get('general-ledger', [\App\Http\Controllers\AccountingController::class, 'generalLedger'])->name('accounting.ledger');
});

// Payables
Route::prefix('payables')->group(function () {
    Route::get('vendors', [\App\Http\Controllers\PayablesController::class, 'vendors'])->name('payables.vendors');
    Route::get('vendors/{id}', [\App\Http\Controllers\PayablesController::class, 'vendorDetail'])->name('payables.vendors.show');
    Route::get('bills', [\App\Http\Controllers\PayablesController::class, 'bills'])->name('payables.bills');
    Route::post('bills', [\App\Http\Controllers\PayablesController::class, 'storeBill'])->name('payables.bills.store');
    Route::get('bills/{id}', [\App\Http\Controllers\PayablesController::class, 'billDetail'])->name('payables.bills.show');
    Route::patch('bills/{id}/approve', [\App\Http\Controllers\PayablesController::class, 'approveBill'])->name('payables.bills.approve');
    Route::get('payments', [\App\Http\Controllers\PayablesController::class, 'payments'])->name('payables.payments');
});

// Receivables
Route::prefix('receivables')->group(function () {
    Route::get('customers', [\App\Http\Controllers\ReceivablesController::class, 'customers'])->name('receivables.customers');
    Route::get('customers/{id}', [\App\Http\Controllers\ReceivablesController::class, 'customerDetail'])->name('receivables.customers.show');
    Route::get('invoices', [\App\Http\Controllers\ReceivablesController::class, 'invoices'])->name('receivables.invoices');
    Route::post('invoices', [\App\Http\Controllers\ReceivablesController::class, 'storeInvoice'])->name('receivables.invoices.store');
    Route::get('invoices/{id}', [\App\Http\Controllers\ReceivablesController::class, 'invoiceDetail'])->name('receivables.invoices.show');
    Route::get('receipts', [\App\Http\Controllers\ReceivablesController::class, 'receipts'])->name('receivables.receipts');
});

// Treasury
Route::prefix('treasury')->group(function () {
    Route::get('bank-accounts', [\App\Http\Controllers\TreasuryController::class, 'bankAccounts'])->name('treasury.banks');
    Route::get('reconciliation', [\App\Http\Controllers\TreasuryController::class, 'reconciliation'])->name('treasury.reconciliation');
    Route::get('cash-forecast', [\App\Http\Controllers\TreasuryController::class, 'cashForecast'])->name('treasury.forecast');
});

// Reports
Route::prefix('reports')->group(function () {
    Route::get('profit-loss', [\App\Http\Controllers\ReportsController::class, 'profitLoss'])->name('reports.pl');
    Route::get('balance-sheet', [\App\Http\Controllers\ReportsController::class, 'balanceSheet'])->name('reports.bs');
    Route::get('cash-flow', [\App\Http\Controllers\ReportsController::class, 'cashFlow'])->name('reports.cf');
    Route::get('audit-trail', [\App\Http\Controllers\ReportsController::class, 'auditTrail'])->name('reports.audit');
});

// Settings
Route::prefix('settings')->group(function () {
    Route::get('company', [\App\Http\Controllers\SettingsController::class, 'company'])->name('settings.company');
    Route::post('company', [\App\Http\Controllers\SettingsController::class, 'updateCompany'])->name('settings.company.update');
    Route::get('users', [\App\Http\Controllers\SettingsController::class, 'users'])->name('settings.users');
    Route::get('roles', [\App\Http\Controllers\SettingsController::class, 'roles'])->name('settings.roles');
    Route::post('roles', [\App\Http\Controllers\SettingsController::class, 'storeRole'])->name('settings.roles.store');
    Route::get('preferences', [\App\Http\Controllers\SettingsController::class, 'preferences'])->name('settings.preferences');
});

// Conversational / WhatsApp Gateway (Webhook — excluded from CSRF by default via VerifyCsrfToken)
Route::post('webhooks/conversational', \App\Http\Controllers\ConversationalGatewayController::class)->name('webhooks.conversational');

// Internal API routes
Route::prefix('api')->group(function () {
    Route::post('ask-ai', [\App\Http\Controllers\ApiController::class, 'askAI']);
    Route::get('dashboard', [\App\Http\Controllers\ApiDashboardController::class, 'index']);
    Route::get('accounting/chart-of-accounts', [\App\Http\Controllers\ApiChartOfAccountsController::class, 'index']);
});

require __DIR__.'/auth.php';
