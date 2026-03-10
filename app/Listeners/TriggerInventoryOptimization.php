<?php

namespace App\Listeners;

use App\Events\InventoryUpdated;
use App\Jobs\ExecuteBusinessAgent;
use App\Agents\Business\InventoryOptimizationAgent;

class TriggerInventoryOptimization
{
    /**
     * Handle the event.
     */
    public function handle(InventoryUpdated $event): void
    {
        // Só dispara se a quantidade mudou (para não sobrecarregar com cada edição simples)
        // O Laravel nos dá o item atualizado.
        ExecuteBusinessAgent::dispatch(
            InventoryOptimizationAgent::class,
            ['item' => $event->item]
        );
    }
}
