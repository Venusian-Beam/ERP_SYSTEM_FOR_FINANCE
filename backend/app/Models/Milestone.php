<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Milestone extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'project_id', 'name', 'description', 'due_date', 'status', 'progress'];

    protected $casts = [
        'due_date' => 'date',
        'progress' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
