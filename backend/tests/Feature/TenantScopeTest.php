<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FinancialAccount;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_scope_filters_financial_accounts(): void
    {
        $tenantA = Tenant::query()->create(['name' => 'Tenant A', 'slug' => 'tenant-a', 'status' => 'active']);
        $tenantB = Tenant::query()->create(['name' => 'Tenant B', 'slug' => 'tenant-b', 'status' => 'active']);

        TenantContext::set((int) $tenantA->id);
        FinancialAccount::query()->create(['code' => '1000', 'name' => 'Cash', 'type' => 'asset', 'normal_balance' => 'debit']);

        TenantContext::set((int) $tenantB->id);
        FinancialAccount::query()->create(['code' => '2000', 'name' => 'Payables', 'type' => 'liability', 'normal_balance' => 'credit']);

        $this->assertSame(['2000'], FinancialAccount::query()->pluck('code')->all());

        TenantContext::clear();
    }
}
