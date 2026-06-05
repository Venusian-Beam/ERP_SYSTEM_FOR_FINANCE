<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TeamMember extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'project_id', 'name', 'email', 'phone', 'role', 'hourly_rate', 'avatar_url', 'status'];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
