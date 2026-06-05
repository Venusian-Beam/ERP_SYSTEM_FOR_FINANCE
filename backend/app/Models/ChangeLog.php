<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ChangeLog extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'project_id', 'title', 'description', 'change_type', 'priority', 'status', 'requested_by', 'approved_by', 'approved_at'];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
