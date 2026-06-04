<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditPackage;
use App\Models\JournalEntry;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

final class AuditPackageCompiler
{
    public function compile(AuditPackage $package): AuditPackage
    {
        TenantContext::set((int) $package->tenant_id);

        try {
            $package->forceFill(['status' => 'processing'])->save();

            $entries = JournalEntry::query()
                ->with('lines.account')
                ->whereBetween('posted_at', [$package->period_start, $package->period_end])
                ->orderBy('posted_at')
                ->get();

            $directory = 'tenants/'.$package->tenant_id.'/audit-packages/'.$package->id;
            $manifestPath = $directory.'/manifest.json';
            $archivePath = $directory.'/audit-package.zip';
            $disk = Storage::disk((string) config('erp.tenant_storage_disk', 'local'));

            $disk->put($manifestPath, json_encode([
                'period_start' => $package->period_start->toDateString(),
                'period_end' => $package->period_end->toDateString(),
                'entries' => $entries->toArray(),
            ], JSON_PRETTY_PRINT));

            $absoluteArchivePath = storage_path('app/'.$archivePath);
            if (! is_dir(dirname($absoluteArchivePath))) {
                mkdir(dirname($absoluteArchivePath), 0755, true);
            }

            $zip = new ZipArchive();
            $zip->open($absoluteArchivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $zip->addFromString('manifest.json', $disk->get($manifestPath));
            $zip->close();

            $package->forceFill([
                'status' => 'complete',
                'archive_path' => $archivePath,
                'metadata' => ['entry_count' => $entries->count()],
            ])->save();

            return $package;
        } finally {
            TenantContext::clear();
        }
    }
}
