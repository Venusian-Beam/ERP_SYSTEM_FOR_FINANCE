<?php

use Illuminate\Support\Facades\Route;

// Standard API Routes for the Vue Application
// Note: These routes are mapped under the /api prefix by bootstrap/app.php

Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

Route::prefix('auth')->group(function () {
    Route::post('register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [\App\Http\Controllers\Api\AuthController::class, 'me']);
        Route::post('logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    });
});

$finance = \App\Http\Controllers\Api\FinanceDataController::class;

Route::get('dashboard', [$finance, 'dashboard']);

Route::prefix('accounting')->group(function () use ($finance) {
    Route::get('chart-of-accounts', [$finance, 'chartOfAccounts']);
    Route::get('accounts', [$finance, 'chartOfAccounts']);
    Route::get('journal-entries', [$finance, 'journalEntries']);
    Route::get('general-ledger', [$finance, 'generalLedger']);
});

Route::prefix('payables')->group(function () use ($finance) {
    Route::get('vendors', [$finance, 'vendors']);
    Route::get('bills', [$finance, 'bills']);
    Route::get('payments', [$finance, 'payments']);
});

Route::prefix('receivables')->group(function () use ($finance) {
    Route::get('customers', [$finance, 'customers']);
    Route::get('invoices', [$finance, 'receivableInvoices']);
    Route::get('receipts', [$finance, 'receipts']);
});

Route::prefix('treasury')->group(function () use ($finance) {
    Route::get('bank-accounts', [$finance, 'bankAccounts']);
    Route::get('reconciliation', [$finance, 'reconciliation']);
    Route::get('cash-forecast', [$finance, 'cashForecast']);
});

Route::prefix('reports')->group(function () use ($finance) {
    Route::get('profit-loss', [$finance, 'profitLoss']);
    Route::get('balance-sheet', [$finance, 'balanceSheet']);
    Route::get('cash-flow', [$finance, 'cashFlow']);
    Route::get('audit-trail', [$finance, 'auditTrail']);
});

Route::prefix('settings')->group(function () use ($finance) {
    Route::get('users', [$finance, 'users']);
    Route::get('roles', [$finance, 'roles']);
});

Route::apiResource('suppliers', \App\Http\Controllers\Api\SupplierController::class);
Route::apiResource('invoices', \App\Http\Controllers\Api\InvoiceController::class);
Route::apiResource('projects', \App\Http\Controllers\Api\ProjectController::class);
Route::apiResource('project-tasks', \App\Http\Controllers\Api\ProjectTaskController::class);
