<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

final class CustomerInvoice extends Model
{
    use Auditable, BelongsToTenant;

    protected $fillable = ['tenant_id', 'customer_id', 'invoice_number', 'invoice_date', 'due_date', 'amount', 'paid_amount', 'status', 'is_finalized'];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'is_finalized' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $invoice): void {
            if (! $invoice->getOriginal('is_finalized')) {
                return;
            }

            $frozen = ['customer_id', 'invoice_number', 'invoice_date', 'due_date', 'amount'];
            foreach ($frozen as $column) {
                if ($invoice->isDirty($column)) {
                    throw new RuntimeException('Finalized invoices are immutable. Use credit or debit notes for adjustments.');
                }
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CreditDebitNote::class);
    }
}
