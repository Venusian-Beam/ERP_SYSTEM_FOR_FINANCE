<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Public
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

Route::prefix('auth')->group(function () {
    Route::post('register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('forgot-password', [\App\Http\Controllers\Api\AuthController::class, 'forgotPassword']);
});

// Protected — requires authentication + tenant scope
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::get('auth/me', [\App\Http\Controllers\Api\AuthController::class, 'me']);
    Route::post('auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);

    Route::post('ask-ai', [\App\Http\Controllers\ApiController::class, 'askAI']);

    // Finance
    $finance = \App\Http\Controllers\Api\FinanceDataController::class;

    Route::get('dashboard', [$finance, 'dashboard']);

    Route::prefix('accounting')->group(function () use ($finance) {
        Route::get('chart-of-accounts', [$finance, 'chartOfAccounts']);
        Route::get('accounts', [$finance, 'chartOfAccounts']);
        Route::post('accounts', [$finance, 'createAccount']);
        Route::put('accounts/{financialAccount}', [$finance, 'updateAccount']);
        Route::delete('accounts/{financialAccount}', [$finance, 'deleteAccount']);
        Route::get('journal-entries', [$finance, 'journalEntries']);
        Route::get('journal-entries/{journalEntry}', [$finance, 'journalEntry']);
        Route::post('journal-entries', [$finance, 'createJournalEntry']);
        Route::put('journal-entries/{journalEntry}', [$finance, 'updateJournalEntry']);
        Route::delete('journal-entries/{journalEntry}', [$finance, 'deleteJournalEntry']);
        Route::get('general-ledger', [$finance, 'generalLedger']);
    });

    Route::prefix('payables')->group(function () use ($finance) {
        Route::get('vendors', [$finance, 'vendors']);
        Route::get('vendors/{supplier}', [$finance, 'vendor']);
        Route::post('vendors', [$finance, 'createVendor']);
        Route::put('vendors/{supplier}', [$finance, 'updateVendor']);
        Route::delete('vendors/{supplier}', [$finance, 'deleteVendor']);
        Route::get('bills', [$finance, 'bills']);
        Route::get('bills/{supplierInvoice}', [$finance, 'bill']);
        Route::post('bills', [$finance, 'createBill']);
        Route::put('bills/{supplierInvoice}', [$finance, 'updateBill']);
        Route::delete('bills/{supplierInvoice}', [$finance, 'deleteBill']);
        Route::get('payments', [$finance, 'payments']);
        Route::get('payments/{payment}', [$finance, 'payment']);
        Route::post('payments', [$finance, 'createPayment']);
        Route::put('payments/{payment}', [$finance, 'updatePayment']);
        Route::delete('payments/{payment}', [$finance, 'deletePayment']);
    });

    Route::prefix('receivables')->group(function () use ($finance) {
        Route::get('customers', [$finance, 'customers']);
        Route::get('customers/{customer}', [$finance, 'customer']);
        Route::post('customers', [$finance, 'createCustomer']);
        Route::put('customers/{customer}', [$finance, 'updateCustomer']);
        Route::delete('customers/{customer}', [$finance, 'deleteCustomer']);
        Route::get('invoices', [$finance, 'receivableInvoices']);
        Route::post('invoices', [$finance, 'createReceivableInvoice']);
        Route::put('invoices/{customerInvoice}', [$finance, 'updateReceivableInvoice']);
        Route::delete('invoices/{customerInvoice}', [$finance, 'deleteReceivableInvoice']);
        Route::get('receipts', [$finance, 'receipts']);
        Route::get('receipts/{payment}', [$finance, 'receipt']);
        Route::post('receipts', [$finance, 'createReceipt']);
        Route::put('receipts/{payment}', [$finance, 'updateReceipt']);
        Route::delete('receipts/{payment}', [$finance, 'deleteReceipt']);
    });

    Route::prefix('treasury')->group(function () use ($finance) {
        Route::get('bank-accounts', [$finance, 'bankAccounts']);
        Route::post('bank-accounts', [$finance, 'createBankAccount']);
        Route::put('bank-accounts/{bankAccount}', [$finance, 'updateBankAccount']);
        Route::delete('bank-accounts/{bankAccount}', [$finance, 'deleteBankAccount']);
        Route::get('reconciliation', [$finance, 'reconciliation']);
        Route::post('reconciliation', [$finance, 'createReconciliation']);
        Route::put('reconciliation/{bankTransaction}', [$finance, 'updateReconciliation']);
        Route::get('cash-forecast', [$finance, 'cashForecast']);
    });

    Route::prefix('reports')->group(function () use ($finance) {
        Route::get('profit-loss', [$finance, 'profitLoss']);
        Route::get('balance-sheet', [$finance, 'balanceSheet']);
        Route::get('cash-flow', [$finance, 'cashFlow']);
        Route::get('audit-trail', [$finance, 'auditTrail']);
    });

    // Settings — full CRUD
    $settings = \App\Http\Controllers\Api\SettingsController::class;
    Route::prefix('settings')->group(function () use ($settings) {
        Route::get('users', [$finance, 'users']);
        Route::get('roles', [$finance, 'roles']);
        Route::get('preferences', [$settings, 'preferences']);
        Route::put('preferences', [$settings, 'updatePreferences']);
        Route::get('company', [$settings, 'company']);
        Route::put('company', [$settings, 'updateCompany']);
    });

    // Core API resources
    Route::apiResource('suppliers', \App\Http\Controllers\Api\SupplierController::class);
    Route::apiResource('invoices', \App\Http\Controllers\Api\InvoiceController::class);
    Route::apiResource('projects', \App\Http\Controllers\Api\ProjectController::class);
    Route::apiResource('project-tasks', \App\Http\Controllers\Api\ProjectTaskController::class);

    // Workspace Resources
    $resources = \App\Http\Controllers\Api\ResourcesController::class;
    Route::prefix('resources')->group(function () use ($resources) {
        Route::get('members', [$resources, 'members']);
        Route::post('members', [$resources, 'storeMember']);
        Route::put('members/{teamMember}', [$resources, 'updateMember']);
        Route::delete('members/{teamMember}', [$resources, 'destroyMember']);
        Route::get('time-entries', [$resources, 'timeEntries']);
        Route::post('time-entries', [$resources, 'storeTimeEntry']);
        Route::put('time-entries/{timeEntry}', [$resources, 'updateTimeEntry']);
        Route::delete('time-entries/{timeEntry}', [$resources, 'destroyTimeEntry']);
        Route::get('milestones', [$resources, 'milestones']);
        Route::post('milestones', [$resources, 'storeMilestone']);
        Route::put('milestones/{milestone}', [$resources, 'updateMilestone']);
        Route::delete('milestones/{milestone}', [$resources, 'destroyMilestone']);
        Route::get('budget', [$resources, 'budget']);
        Route::post('budget/expenses', [$resources, 'storeExpense']);
    });

    // Quality
    $quality = \App\Http\Controllers\Api\QualityController::class;
    Route::prefix('quality')->group(function () use ($quality) {
        Route::get('test-cases', [$quality, 'testCases']);
        Route::post('test-cases', [$quality, 'storeTestCase']);
        Route::put('test-cases/{qaTest}', [$quality, 'updateTestCase']);
        Route::delete('test-cases/{qaTest}', [$quality, 'destroyTestCase']);
        Route::post('test-cases/{qaTest}/run', [$quality, 'runTestCase']);
        Route::get('risks', [$quality, 'risks']);
        Route::post('risks', [$quality, 'storeRisk']);
        Route::put('risks/{risk}', [$quality, 'updateRisk']);
        Route::get('change-logs', [$quality, 'changeLogs']);
        Route::post('change-logs', [$quality, 'storeChangeLog']);
        Route::post('change-logs/{changeLog}/approve', [$quality, 'approveChangeLog']);
        Route::post('change-logs/{changeLog}/reject', [$quality, 'rejectChangeLog']);
    });

    // Initiation
    $initiation = \App\Http\Controllers\Api\InitiationController::class;
    Route::prefix('initiation')->group(function () use ($initiation) {
        Route::get('stakeholders', [$initiation, 'stakeholders']);
        Route::post('stakeholders', [$initiation, 'storeStakeholder']);
        Route::put('stakeholders/{stakeholder}', [$initiation, 'updateStakeholder']);
        Route::delete('stakeholders/{stakeholder}', [$initiation, 'destroyStakeholder']);
        Route::get('kickoffs', [$initiation, 'kickoffs']);
        Route::post('kickoffs', [$initiation, 'storeKickoff']);
    });

    // Agile
    $agile = \App\Http\Controllers\Api\AgileController::class;
    Route::prefix('agile')->group(function () use ($agile) {
        Route::get('sprints', [$agile, 'sprints']);
        Route::post('sprints', [$agile, 'storeSprint']);
        Route::put('sprints/{sprint}', [$agile, 'updateSprint']);
        Route::get('backlog', [$agile, 'backlog']);
        Route::post('backlog', [$agile, 'storeBacklogItem']);
        Route::put('backlog/{backlogItem}', [$agile, 'updateBacklogItem']);
        Route::delete('backlog/{backlogItem}', [$agile, 'destroyBacklogItem']);
        Route::get('definitions', [$agile, 'definitions']);
        Route::put('definitions', [$agile, 'updateDefinitions']);
    });

    // Communication
    $comm = \App\Http\Controllers\Api\CommunicationController::class;
    Route::prefix('communication')->group(function () use ($comm) {
        Route::get('messages', [$comm, 'messages']);
        Route::post('messages', [$comm, 'storeMessage']);
        Route::post('messages/{chatMessage}/read', [$comm, 'markAsRead']);
    });
});
