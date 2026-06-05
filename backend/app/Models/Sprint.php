<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Sprint extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'project_id', 'name', 'goal', 'start_date', 'end_date', 'status', 'velocity'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'velocity' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function backlogItems(): HasMany
    {
        return $this->hasMany(BacklogItem::class);
    }
}
