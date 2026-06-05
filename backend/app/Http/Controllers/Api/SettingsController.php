<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function preferences(): JsonResponse
    {
        $setting = CompanySetting::query()
            ->where('tenant_id', TenantContext::requireId())
            ->first();

        return response()->json([
            'timezone' => $setting?->timezone ?? 'UTC',
            'date_format' => $setting?->date_format ?? 'Y-m-d',
            'currency' => $setting?->currency ?? 'USD',
            'notification_channels' => $setting?->notification_channels ?? [],
        ]);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'timezone' => 'nullable|string|max:50',
            'date_format' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:10',
            'notification_channels' => 'nullable|array',
            'notification_channels.*' => 'string',
        ]);

        $setting = CompanySetting::query()
            ->where('tenant_id', TenantContext::requireId())
            ->firstOrCreate(
                ['tenant_id' => TenantContext::requireId()],
                ['company_name' => 'My Company']
            );

        $setting->update($validated);

        return response()->json($setting);
    }

    public function company(): JsonResponse
    {
        $setting = CompanySetting::query()
            ->where('tenant_id', TenantContext::requireId())
            ->firstOrCreate(
                ['tenant_id' => TenantContext::requireId()],
                ['company_name' => 'My Company']
            );

        return response()->json($setting);
    }

    public function updateCompany(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'currency' => 'nullable|string|max:10',
            'fiscal_year_start' => 'nullable|string|max:5',
        ]);

        $setting = CompanySetting::query()
            ->where('tenant_id', TenantContext::requireId())
            ->firstOrCreate(
                ['tenant_id' => TenantContext::requireId()],
                ['company_name' => 'My Company']
            );

        $setting->update($validated);

        return response()->json($setting);
    }
}
