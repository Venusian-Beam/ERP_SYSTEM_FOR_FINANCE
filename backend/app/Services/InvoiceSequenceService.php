<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CustomerInvoice;
use Illuminate\Support\Facades\DB;

final class InvoiceSequenceService
{
    public function nextCustomerInvoiceNumber(): string
    {
        $lastNumber = CustomerInvoice::query()
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('invoice_number');

        $next = 1;
        if (is_string($lastNumber) && preg_match('/INV-(\d+)/', $lastNumber, $matches) === 1) {
            $next = ((int) $matches[1]) + 1;
        }

        return 'INV-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @template TReturn
     * @param callable(): TReturn $callback
     * @return TReturn
     */
    public function insideSequenceLock(callable $callback): mixed
    {
        return DB::transaction(fn (): mixed => $callback());
    }
}
