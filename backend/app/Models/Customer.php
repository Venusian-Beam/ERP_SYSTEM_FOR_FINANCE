<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Customer extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'email', 'phone', 'status'];

    public function invoices(): HasMany
    {
        return $this->hasMany(CustomerInvoice::class);
    }
}
