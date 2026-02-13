<?php

namespace App\Observers;

use App\Models\OrdemServico;
use App\Models\VehicleHistory;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrdemServicoObserver
{
    /**
     * Handle the OrdemServico "updated" event.
     */
    public function updated(OrdemServico $ordemServico): void
    {
        // 1. Notificações (E-mail e WhatsApp)
        if ($ordemServico->isDirty('status')) {
            $client = $ordemServico->client;
            
            if ($client) {
                if ($client->email) {
                    Log::info("Enviando e-mail para {$client->email}: Sua OS #{$ordemServico->id} mudou para {$ordemServico->status}");
                }
                
                if ($client->whatsapp || $client->phone) {
                    $phone = $client->whatsapp ?? $client->phone;
                    Log::info("Enviando WhatsApp para {$phone}: Status atualizado.");
                }
            }
        }

        // 2. Finalização (Histórico e Financeiro)
        if ($ordemServico->isDirty('status') && 
            in_array($ordemServico->status, ['finalized', 'completed', 'Finalizada'])) {
            
            // Histórico do Veículo
            if ($ordemServico->veiculo_id) {
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

            // Lançamento Financeiro
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