<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class BankAccount extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'account_name', 'account_number', 'bank_name',
        'currency', 'current_balance', 'available_balance', 'status', 'color_hex',
    ];

    protected $casts = [
        'current_balance'   => 'decimal:2',
        'available_balance' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(Reconciliation::class);
    }
}
