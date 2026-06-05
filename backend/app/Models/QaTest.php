<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class QaTest extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'project_id', 'title', 'description', 'test_type', 'steps', 'expected_result', 'status', 'assigned_to', 'priority'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
