<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\SupplierInvoice;
use Illuminate\Support\Facades\Cache;

final class RealTimeRuleEvaluator
{
    public function evaluate(JournalEntry $entry): void
    {
        $singleLimit = (float) config('erp.fraud_rules.single_transaction_limit');
        $dailyLimit = (float) config('erp.fraud_rules.daily_account_limit');
        $deviationMultiplier = (float) config('erp.fraud_rules.deviation_multiplier', 2.0);
        $tenantAverage = (float) JournalLine::query()
            ->whereHas('entry', fn ($query) => $query->where('posted_at', '>=', now()->subDays(30)))
            ->selectRaw('AVG(debit + credit) as average_amount')
            ->value('average_amount');

        foreach ($entry->lines as $line) {
            $amount = max((float) $line->debit, (float) $line->credit);
            $cacheKey = 'ledger:daily:'.$line->tenant_id.':'.$line->financial_account_id.':'.$entry->posted_at->toDateString();
            $runningTotal = (float) Cache::get($cacheKey, 0) + $amount;

            Cache::put($cacheKey, $runningTotal, now()->addDay());

            if ($amount > $singleLimit || $runningTotal > $dailyLimit || ($tenantAverage > 0 && $amount > ($tenantAverage * $deviationMultiplier))) {
                $line->forceFill(['state' => 'flagged_pending_review'])->save();
            }
        }
    }

    public function evaluateSupplierInvoice(SupplierInvoice $invoice): void
    {
        $highValueThreshold = (float) config('erp.fraud_rules.high_value_threshold', 1000);
        $velocityLimit = (int) config('erp.fraud_rules.velocity_limit_count', 5);
        $velocityWindow = (int) config('erp.fraud_rules.velocity_window_minutes', 360);
        $splitCount = (int) config('erp.fraud_rules.invoice_split_count', 3);
        $splitWindow = (int) config('erp.fraud_rules.invoice_split_window_minutes', 1440);
        $splitLimit = (float) config('erp.fraud_rules.invoice_split_limit', 1000);

        if ((float) $invoice->amount >= $highValueThreshold) {
            $velocityKey = 'ap:velocity:'.$invoice->tenant_id.':'.$invoice->supplier_id;
            $count = (int) Cache::get($velocityKey, 0) + 1;
            Cache::put($velocityKey, $count, now()->addMinutes($velocityWindow));

            if ($count > $velocityLimit) {
                $invoice->forceFill(['status' => 'flagged_pending_review'])->save();
            }
        }

        if ((float) $invoice->amount < $splitLimit) {
            $splitKey = 'ap:split:'.$invoice->tenant_id.':'.$invoice->supplier_id;
            $count = (int) Cache::get($splitKey, 0) + 1;
            Cache::put($splitKey, $count, now()->addMinutes($splitWindow));

            if ($count >= $splitCount) {
                $invoice->forceFill(['status' => 'flagged_pending_review'])->save();
            }
        }
    }
}
