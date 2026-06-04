<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\JournalEntryPosted;
use App\Listeners\EvaluateJournalEntryRules;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

final class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        JournalEntryPosted::class => [
            EvaluateJournalEntryRules::class,
        ],
    ];
}
