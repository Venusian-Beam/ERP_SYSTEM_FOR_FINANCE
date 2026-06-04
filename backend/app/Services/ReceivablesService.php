<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CustomerInvoice;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ReceivablesService
{
    /**
     * @param array<string, mixed> $payload
     */
    public function recordPayment(CustomerInvoice $invoice, array $payload): CustomerInvoice
    {
        return DB::transaction(function () use ($invoice, $payload): CustomerInvoice {
            $outstanding = (float) $invoice->amount - (float) $invoice->paid_amount;
            $paymentAmount = (float) $payload['amount'];

            if ($paymentAmount > $outstanding) {
                throw new InvalidArgumentException('Payment exceeds outstanding invoice balance. Route unallocated cash to clearing account.');
            }

            $invoice->payments()->create([
                'amount' => $paymentAmount,
                'paid_at' => $payload['paid_at'],
                'reference' => $payload['reference'],
                'method' => $payload['method'] ?? null,
            ]);

            $paidAmount = (float) $invoice->payments()->sum('amount');
            $status = $paidAmount >= (float) $invoice->amount ? 'paid' : 'partially_paid';

            $invoice->forceFill([
                'paid_amount' => $paidAmount,
                'status' => $status,
            ])->save();

            return $invoice->refresh()->load(['customer', 'payments']);
        });
    }
}
