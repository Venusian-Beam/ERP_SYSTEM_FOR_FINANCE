<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AuditPackage;
use App\Services\AuditPackageCompiler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class CompileAuditPackage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly int $auditPackageId, public readonly int $tenantId)
    {
    }

    public function handle(AuditPackageCompiler $compiler): void
    {
        $package = AuditPackage::withoutGlobalScopes()
            ->where('tenant_id', $this->tenantId)
            ->findOrFail($this->auditPackageId);

        $compiler->compile($package);
    }
}
