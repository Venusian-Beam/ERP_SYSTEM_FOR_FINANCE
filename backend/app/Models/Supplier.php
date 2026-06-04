<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Supplier extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'email', 'phone', 'tax_identifier', 'status'];

    public function invoices(): HasMany
    {
        return $this->hasMany(SupplierInvoice::class);
    }
}
