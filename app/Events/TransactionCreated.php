<?php

namespace App\Events;

use App\Models\FinancialTransaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public FinancialTransaction $transaction;

    /**
     * Create a new event instance.
     */
    public function __construct(FinancialTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
