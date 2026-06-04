<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

final class AuditPackage extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'period_start', 'period_end', 'status', 'archive_path', 'metadata'];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'metadata' => 'array',
    ];
}
