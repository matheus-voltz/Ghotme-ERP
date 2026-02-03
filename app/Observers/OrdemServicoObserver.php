<?php

namespace App\Observers;

use App\Models\OrdemServico;
use App\Models\VehicleHistory;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Auth;

class OrdemServicoObserver
{
    /**
     * Handle the OrdemServico "updated" event.
     */
    public function updated(OrdemServico $ordemServico): void
    {
        // Verifica se o status mudou para 'finalized' ou 'completed' (ajuste conforme seu padrão)
        // E se a OS possui um veículo vinculado
        if ($ordemServico->isDirty('status') && 
            in_array($ordemServico->status, ['finalized', 'completed', 'Finalizada'])) {
            
            if ($ordemServico->veiculo_id) {
                // 1. Cria o registro no histórico automaticamente
                VehicleHistory::create([
                    'veiculo_id' => $ordemServico->veiculo_id,
                    'ordem_servico_id' => $ordemServico->id,
                    'date' => now(),
                    'km' => $ordemServico->km_entry ?? 0,
                    'event_type' => 'os_finalizada',
                    'title' => 'Ordem de Serviço Finalizada #' . $ordemServico->id,
                    'description' => $ordemServico->description,
                    'performer' => 'Oficina Interna',
                    'created_by' => Auth::id() ?? $ordemServico->user_id,
                ]);
            }

            // 2. Cria lançamento financeiro no Contas a Receber
            FinancialTransaction::create([
                'description' => "Serviço OS #{$ordemServico->id}",
                'amount' => $ordemServico->total,
                'type' => 'in',
                'status' => 'pending',
                'due_date' => now(),
                'client_id' => $ordemServico->client_id,
                'related_type' => 'App\Models\OrdemServico',
                'related_id' => $ordemServico->id,
                'category' => 'Serviços',
                'user_id' => Auth::id() ?? $ordemServico->user_id,
            ]);
        }
    }
}
