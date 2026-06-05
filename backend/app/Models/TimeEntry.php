<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TimeEntry extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'project_id', 'team_member_id', 'description', 'hours', 'date', 'billable', 'approved'];

    protected $casts = [
        'hours' => 'decimal:2',
        'date' => 'date',
        'billable' => 'boolean',
        'approved' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }
}
