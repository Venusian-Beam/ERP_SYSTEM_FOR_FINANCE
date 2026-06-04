<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('manage-users', fn (User $user): bool => $user->hasRole('tenant_admin'));
        Gate::define('approve-financials', fn (User $user): bool => $user->hasAnyRole(['tenant_admin', 'finance_manager']));
        Gate::define('create-financials', fn (User $user): bool => $user->hasAnyRole(['tenant_admin', 'finance_manager', 'financial_clerk']));
        Gate::define('compile-audit-packages', fn (User $user): bool => $user->hasAnyRole(['tenant_admin', 'auditor']));
        Gate::define('read-ledger', fn (User $user): bool => $user->hasAnyRole(['tenant_admin', 'finance_manager', 'financial_clerk', 'auditor']));
    }
}
