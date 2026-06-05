<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Stakeholder extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'project_id', 'name', 'email', 'phone', 'role', 'influence', 'interest', 'expectations', 'status'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
