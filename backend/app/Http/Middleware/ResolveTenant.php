<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $userTenantId = $request->user()?->tenant_id;
        $host = $request->getHost();
        $centralDomains = (array) config('erp.central_domains', ['localhost', '127.0.0.1']);
        $subdomain = $this->subdomainFromHost($host, $centralDomains);

        $tenant = null;

        if ($subdomain !== null) {
            $tenant = Tenant::query()->where('slug', $subdomain)->where('status', 'active')->first();
        } elseif ($userTenantId !== null) {
            $tenant = Tenant::query()->whereKey($userTenantId)->where('status', 'active')->first();
        }

        abort_if($tenant === null, 403, 'Unable to resolve active tenant.');
        abort_if($userTenantId !== null && (int) $userTenantId !== (int) $tenant->id, 403, 'Authenticated user does not belong to this tenant.');

        TenantContext::set((int) $tenant->id);
        $request->session()->put('active_tenant_id', (int) $tenant->id);

        try {
            return $next($request);
        } finally {
            TenantContext::clear();
        }
    }

    /**
     * @param array<int, string> $centralDomains
     */
    private function subdomainFromHost(string $host, array $centralDomains): ?string
    {
        foreach ($centralDomains as $domain) {
            if ($host === $domain) {
                return null;
            }

            $suffix = '.'.$domain;
            if (str_ends_with($host, $suffix)) {
                return substr($host, 0, -strlen($suffix)) ?: null;
            }
        }

        return null;
    }
}
