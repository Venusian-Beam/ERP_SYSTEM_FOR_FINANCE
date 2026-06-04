<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FinancialAccount;
use Illuminate\Database\Seeder;

final class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ((array) config('erp.chart_of_accounts', []) as $account) {
            FinancialAccount::query()->firstOrCreate(
                ['code' => $account['code']],
                [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'normal_balance' => $account['normal_balance'],
                    'is_active' => true,
                ],
            );
        }
    }
}
