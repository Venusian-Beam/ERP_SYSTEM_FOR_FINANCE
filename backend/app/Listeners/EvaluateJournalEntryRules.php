<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\JournalEntryPosted;
use App\Services\RealTimeRuleEvaluator;

final class EvaluateJournalEntryRules
{
    public function __construct(private readonly RealTimeRuleEvaluator $evaluator)
    {
    }

    public function handle(JournalEntryPosted $event): void
    {
        $this->evaluator->evaluate($event->entry);
    }
}
