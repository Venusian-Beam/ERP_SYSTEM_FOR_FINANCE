<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ApprovalStep extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'supplier_invoice_id', 'sequence', 'approver_id', 'approver_role', 'status', 'acted_at', 'notes'];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class, 'supplier_invoice_id');
    }
}
