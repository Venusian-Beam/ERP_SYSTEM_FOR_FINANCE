<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Reconciliation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'bank_account_id', 'statement_date',
        'statement_closing_balance', 'status',
        'completed_by', 'completed_at',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'statement_closing_balance' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(ReconciliationMatch::class);
    }
}
