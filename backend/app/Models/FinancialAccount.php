<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class FinancialAccount extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'code', 'name', 'type', 'normal_balance', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }
}
