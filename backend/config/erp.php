<?php

declare(strict_types=1);

return [
    'tenant_header' => env('TENANT_HEADER', 'X-Tenant'),
    'central_domains' => array_filter(array_map('trim', explode(',', env('TENANT_CENTRAL_DOMAINS', 'localhost,127.0.0.1')))),
    'tenant_storage_disk' => env('TENANT_STORAGE_DISK', 'local'),
    'roles' => [
        'tenant_admin',
        'finance_manager',
        'financial_clerk',
        'auditor',
    ],
    'chart_of_accounts' => [
        ['code' => '1010', 'name' => 'Cash at Bank', 'type' => 'asset', 'normal_balance' => 'debit'],
        ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'asset', 'normal_balance' => 'debit'],
        ['code' => '2100', 'name' => 'Accounts Payable', 'type' => 'liability', 'normal_balance' => 'credit'],
        ['code' => '2200', 'name' => 'Unallocated Cash Clearing', 'type' => 'liability', 'normal_balance' => 'credit'],
        ['code' => '3010', 'name' => 'Retained Earnings', 'type' => 'equity', 'normal_balance' => 'credit'],
        ['code' => '4010', 'name' => 'Operating Sales', 'type' => 'revenue', 'normal_balance' => 'credit'],
        ['code' => '5010', 'name' => 'Administrative Overhead', 'type' => 'expense', 'normal_balance' => 'debit'],
        ['code' => '5100', 'name' => 'Project Costs', 'type' => 'expense', 'normal_balance' => 'debit'],
    ],
    'ap_workflow' => [
        'tier_1_limit' => 1000,
        'tier_2_limit' => 10000,
        'tier_1_roles' => ['finance_manager'],
        'tier_2_roles' => ['finance_manager', 'tenant_admin'],
        'tier_3_roles' => ['auditor', 'tenant_admin'],
    ],
    'fraud_rules' => [
        'single_transaction_limit' => 100000,
        'daily_account_limit' => 250000,
        'daily_supplier_invoice_limit' => 100000,
        'high_value_threshold' => 1000,
        'velocity_limit_count' => 5,
        'velocity_window_minutes' => 360,
        'invoice_split_count' => 3,
        'invoice_split_window_minutes' => 1440,
        'invoice_split_limit' => 1000,
        'deviation_multiplier' => 2.0,
    ],
];
