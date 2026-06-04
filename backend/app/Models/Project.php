<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Project extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'code', 'status', 'start_date', 'due_date', 'budget_amount', 'progress'];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'budget_amount' => 'decimal:2',
        'progress' => 'integer',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }
}
