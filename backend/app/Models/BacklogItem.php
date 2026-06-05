<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BacklogItem extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'project_id', 'sprint_id', 'title', 'description', 'story_points', 'priority', 'status', 'type', 'assignee'];

    protected $casts = [
        'story_points' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }
}
