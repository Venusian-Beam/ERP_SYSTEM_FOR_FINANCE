<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Kickoff extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'project_id', 'title', 'description', 'meeting_date', 'duration_minutes', 'location', 'agenda', 'minutes', 'status'];

    protected $casts = [
        'meeting_date' => 'date',
        'duration_minutes' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
