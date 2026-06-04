<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ApiChartOfAccountsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = Tenant::query()->where('slug', 'demo')->first();
        if ($tenant) {
            TenantContext::set((int) $tenant->id);
        }

        $accounts = FinancialAccount::query()
            ->withSum(['journalLines as debit_total'], 'debit')
            ->withSum(['journalLines as credit_total'], 'credit')
            ->orderBy('code')
            ->get()
            ->map(function (FinancialAccount $account): FinancialAccount {
                $debits = (float) ($account->debit_total ?? 0);
                $credits = (float) ($account->credit_total ?? 0);

                $account->current_balance = $account->normal_balance === 'credit'
                    ? $credits - $debits
                    : $debits - $credits;

                return $account;
            });

        $totalAssets = $accounts->where('type', 'asset')->sum('current_balance');
        $totalLiabilities = $accounts->where('type', 'liability')->sum('current_balance');
        $totalEquity = $accounts->where('type', 'equity')->sum('current_balance');
        $activeAccounts = $accounts->where('is_active', true)->count();

        $records = $accounts->map(function (FinancialAccount $account): array {
            return [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => ucfirst((string) $account->type),
                'parent' => 'General Ledger',
                'balance' => $account->current_balance,
                'status' => $account->is_active ? 'Active' : 'Inactive',
            ];
        })->values();

        if ($tenant) {
            TenantContext::clear();
        }

        return response()->json([
            'metrics' => [
                [
                    'label' => 'Total accounts',
                    'value' => (string) $accounts->count(),
                    'trend' => "{$activeAccounts} active accounts",
                    'icon' => 'ri-git-branch-line',
                ],
                [
                    'label' => 'Assets',
                    'value' => 'GHC ' . number_format((float) $totalAssets, 2),
                    'trend' => '+6.8% YTD',
                    'icon' => 'ri-safe-line',
                    'tone' => 'success',
                ],
                [
                    'label' => 'Liabilities',
                    'value' => 'GHC ' . number_format((float) $totalLiabilities, 2),
                    'trend' => '34.5% of assets',
                    'icon' => 'ri-scales-3-line',
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Equity',
                    'value' => 'GHC ' . number_format((float) $totalEquity, 2),
                    'trend' => '+9.2% YTD',
                    'icon' => 'ri-funds-line',
                    'tone' => 'success',
                ],
            ],
            'records' => $records,
        ]);
    }
}
