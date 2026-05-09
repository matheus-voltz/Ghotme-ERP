<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait TransactionalOperations
{
    /**
     * Executa operação crítica em transação com retry
     */
    protected function executeInTransaction(callable $callback, int $maxAttempts = 3)
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxAttempts) {
            try {
                return DB::transaction($callback);
            } catch (\Exception $e) {
                $attempt++;
                $lastException = $e;
                
                if ($attempt >= $maxAttempts) {
                    throw $lastException;
                }
                
                // Exponential backoff
                usleep(1000 * (2 ** $attempt));
            }
        }

        throw $lastException;
    }
}
