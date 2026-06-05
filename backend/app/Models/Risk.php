<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Risk extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'project_id', 'title', 'description', 'probability', 'impact', 'severity', 'mitigation', 'contingency', 'status', 'owner'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
