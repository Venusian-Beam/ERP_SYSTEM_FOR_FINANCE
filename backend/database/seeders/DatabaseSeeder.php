<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->firstOrCreate(
            ['slug' => 'demo'],
            ['name' => 'Demo Institution', 'domain' => 'demo.localhost', 'status' => 'active'],
        );

        TenantContext::set((int) $tenant->id);
        $this->call(ChartOfAccountsSeeder::class);
        $this->call(DemoDataSeeder::class);

        User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Tenant Admin',
                'password' => Hash::make('password'),
                'role' => 'tenant_admin',
            ],
        );

        TenantContext::clear();
    }
}
