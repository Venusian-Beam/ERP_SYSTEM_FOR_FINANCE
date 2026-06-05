<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BudgetExpense extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'project_id', 'category', 'description', 'amount', 'date', 'approved'];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'approved' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
