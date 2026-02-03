<?php

namespace App\Observers;

use App\Models\OrdemServico;
use App\Models\VehicleHistory;
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
            in_array($ordemServico->status, ['finalized', 'completed', 'Finalizada']) && 
            $ordemServico->veiculo_id) {
            
            // Cria o registro no histórico automaticamente
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
    }
}
