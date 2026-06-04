<?php

use Illuminate\Support\Facades\Route;

// Standard API Routes for the Vue Application
// Note: These routes are mapped under the /api prefix by bootstrap/app.php

Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// We will add resource routes here as we build out the modules.
Route::apiResource('suppliers', \App\Http\Controllers\Api\SupplierController::class);
Route::apiResource('invoices', \App\Http\Controllers\Api\InvoiceController::class);
Route::apiResource('projects', \App\Http\Controllers\Api\ProjectController::class);
Route::apiResource('project-tasks', \App\Http\Controllers\Api\ProjectTaskController::class);
