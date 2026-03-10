<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use App\Jobs\ExecuteBusinessAgent;
use App\Agents\Business\FinancialAuditorAgent;

class TriggerFinancialAudit
{
    /**
     * Handle the event.
     */
    public function handle(TransactionCreated $event): void
    {
        // Dispatch the job to the queue
        // We pass the class name of the agent and the context needed
        ExecuteBusinessAgent::dispatch(
            FinancialAuditorAgent::class,
            ['transaction' => $event->transaction]
        );
    }
}
