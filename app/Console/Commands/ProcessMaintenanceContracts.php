<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MaintenanceContract;
use App\Models\FinancialTransaction;
use App\Models\OrdemServico;
use Carbon\Carbon;

class ProcessMaintenanceContracts extends Command
{
    protected $signature = 'maintenance:process';
    protected $description = 'Gera cobranças e OSs automáticas para contratos ativos';

    public function handle()
    {
        $today = Carbon::today();
        $contracts = MaintenanceContract::where('status', 'active')
            ->whereDate('next_billing_date', '<=', $today)
            ->get();

        $this->info("Processando {$contracts->count()} contratos...");

        foreach ($contracts as $contract) {
            // 1. Gerar Transação Financeira
            FinancialTransaction::create([
                'company_id' => $contract->company_id,
                'client_id' => $contract->client_id,
                'description' => "Mensalidade Contrato: {$contract->title}",
                'amount' => $contract->amount,
                'type' => 'in',
                'status' => 'pending',
                'due_date' => $contract->next_billing_date,
                'category' => 'Contrato Recorrente'
            ]);

            // 2. Gerar OS Automática (Se configurado)
            if ($contract->auto_generate_os) {
                OrdemServico::create([
                    'company_id' => $contract->company_id,
                    'client_id' => $contract->client_id,
                    'status' => 'pending',
                    'description' => "Manutenção Preventiva Automática - Contrato: {$contract->title}",
                ]);
            }

            // 3. Atualizar Próxima Data
            $nextDate = $contract->next_billing_date->copy();
            if ($contract->frequency === 'monthly') $nextDate->addMonth();
            if ($contract->frequency === 'quarterly') $nextDate->addMonths(3);
            if ($contract->frequency === 'yearly') $nextDate->addYear();

            $contract->update(['next_billing_date' => $nextDate]);
            
            $this->line("Processado: {$contract->title} para o cliente ID {$contract->client_id}");
        }

        $this->info("Concluído!");
    }
}
