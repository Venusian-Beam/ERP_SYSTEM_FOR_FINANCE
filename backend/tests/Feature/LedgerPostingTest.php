<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FinancialAccount;
use App\Models\Tenant;
use App\Services\LedgerPostingService;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

final class LedgerPostingTest extends TestCase
{
    use RefreshDatabase;

    public function test_balanced_journal_entry_posts_atomically(): void
    {
        $tenant = Tenant::query()->create(['name' => 'Tenant', 'slug' => 'tenant', 'status' => 'active']);
        TenantContext::set((int) $tenant->id);

        $cash = FinancialAccount::query()->create(['code' => '1000', 'name' => 'Cash', 'type' => 'asset', 'normal_balance' => 'debit']);
        $revenue = FinancialAccount::query()->create(['code' => '4000', 'name' => 'Revenue', 'type' => 'revenue', 'normal_balance' => 'credit']);

        $entry = app(LedgerPostingService::class)->post([
            'reference' => 'JE-0001',
            'posted_at' => now(),
            'lines' => [
                ['financial_account_id' => $cash->id, 'debit' => 100, 'credit' => 0],
                ['financial_account_id' => $revenue->id, 'debit' => 0, 'credit' => 100],
            ],
        ]);

        $this->assertCount(2, $entry->lines);
        TenantContext::clear();
    }

    public function test_unbalanced_journal_entry_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $tenant = Tenant::query()->create(['name' => 'Tenant', 'slug' => 'tenant', 'status' => 'active']);
        TenantContext::set((int) $tenant->id);

        $cash = FinancialAccount::query()->create(['code' => '1000', 'name' => 'Cash', 'type' => 'asset', 'normal_balance' => 'debit']);
        $revenue = FinancialAccount::query()->create(['code' => '4000', 'name' => 'Revenue', 'type' => 'revenue', 'normal_balance' => 'credit']);

        app(LedgerPostingService::class)->post([
            'reference' => 'JE-0002',
            'posted_at' => now(),
            'lines' => [
                ['financial_account_id' => $cash->id, 'debit' => 100, 'credit' => 0],
                ['financial_account_id' => $revenue->id, 'debit' => 0, 'credit' => 50],
            ],
        ]);
    }
}
