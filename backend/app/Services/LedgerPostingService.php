<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\JournalEntryPosted;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class LedgerPostingService
{
    /**
     * @param array<string, mixed> $payload
     */
    public function post(array $payload): JournalEntry
    {
        return DB::transaction(function () use ($payload): JournalEntry {
            $lines = $payload['lines'] ?? [];
            $debits = array_sum(array_map(static fn (array $line): float => (float) ($line['debit'] ?? 0), $lines));
            $credits = array_sum(array_map(static fn (array $line): float => (float) ($line['credit'] ?? 0), $lines));

            if (round($debits, 2) !== round($credits, 2)) {
                throw new InvalidArgumentException('Journal entries must balance before posting.');
            }

            $entry = JournalEntry::query()->create([
                'reference' => $payload['reference'],
                'posted_at' => $payload['posted_at'],
                'description' => $payload['description'] ?? null,
                'status' => 'posted',
                'created_by' => $payload['created_by'] ?? null,
            ]);

            foreach ($lines as $line) {
                $entry->lines()->create([
                    'financial_account_id' => $line['financial_account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'memo' => $line['memo'] ?? null,
                    'state' => 'posted',
                ]);
            }

            event(new JournalEntryPosted($entry->load('lines.account')));

            return $entry;
        });
    }
}
