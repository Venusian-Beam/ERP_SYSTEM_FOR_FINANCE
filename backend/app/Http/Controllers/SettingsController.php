<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class SettingsController extends Controller
{
    public function company(Request $request): Response
    {
        $settings = CompanySetting::query()->firstOrNew([]);

        return Inertia::render('settings/Company', [
            'settings' => $settings,
        ]);
    }

    public function updateCompany(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'company_name'         => ['required', 'string', 'max:255'],
            'registration_number'  => ['nullable', 'string', 'max:100'],
            'tax_id'               => ['nullable', 'string', 'max:100'],
            'address'              => ['nullable', 'string', 'max:500'],
            'currency'             => ['required', 'string', 'size:3'],
            'fiscal_year_start'    => ['required', 'string', 'regex:/^\d{2}-\d{2}$/'],
            'notification_channels' => ['nullable', 'array'],
        ]);

        CompanySetting::query()->updateOrCreate([], $payload);

        return redirect()->route('settings.company');
    }

    public function users(Request $request): Response
    {
        $tenantId = Auth::user()?->tenant_id;

        $users = User::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->with('roles:id,name,slug')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('settings/Users', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function roles(Request $request): Response
    {
        $roles = Role::query()
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return Inertia::render('settings/Roles', [
            'roles' => $roles,
        ]);
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'slug'        => ['required', 'string', 'max:100', 'unique:roles,slug'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
        ]);

        Role::query()->create($payload);

        return redirect()->route('settings.roles');
    }

    public function preferences(Request $request): Response
    {
        $settings = CompanySetting::query()->first();

        return Inertia::render('settings/Preferences', [
            'settings' => $settings,
        ]);
    }
}
