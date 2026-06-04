<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\CompileAuditPackage;
use App\Models\AuditPackage;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AuditPackageController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ]);

        $package = AuditPackage::query()->create($payload + ['status' => 'queued']);

        CompileAuditPackage::dispatch((int) $package->id, TenantContext::requireId());

        return redirect()->back()->with('status', 'Audit package queued.');
    }
}
