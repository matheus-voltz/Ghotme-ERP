<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \App\Events\TransactionCreated::class => [
            \App\Listeners\TriggerFinancialAudit::class,
        ],
        \App\Events\InventoryUpdated::class => [
            \App\Listeners\TriggerInventoryOptimization::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
