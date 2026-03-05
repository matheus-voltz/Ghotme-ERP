<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Budget;
use App\Models\Company;
use App\Events\NewIFoodOrderEvent;

class ProcessIFoodOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $companyId;
    protected $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(int $companyId, array $payload)
    {
        $this->companyId = $companyId;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $company = Company::find($this->companyId);
        if (!$company || $company->niche !== 'food_service') {
            Log::warning('ProcessIFoodOrderJob: Empresa não encontrada ou nicho incorreto.');
            return;
        }

        $orderId = $this->payload['orderId'] ?? null;
        $eventType = $this->payload['fullCode'] ?? null;

        Log::info("Processando pedido iFood: {$orderId} (Evento: {$eventType}) para empresa {$company->name}");

        // No iFood, o evento 'PLACED' (ou PLC) indica que o pedido acabou de sair do cliente.
        // O sistema deve ser avisado e o pedido deve ser criado como um "Pré-pedido" ou "Orçamento".
        
        if ($eventType === 'PLACED') {
            $this->createNewOrder($company, $orderId);
        }

        // Outros eventos (ex: CONFIRMED, CANCELLED) podem atualizar o status no sistema.
    }

    /**
     * Cria o pedido no banco de dados ERP como um Budget/Orçamento
     */
    private function createNewOrder(Company $company, $orderId)
    {
        // 1. O ideal aqui seria chamar a API do iFood (GET /orders/{id}) para pegar itens e dados do cliente
        // Por enquanto vamos simular a criação básica para avisar o sistema
        
        try {
            // $budget = Budget::create([
            //     'company_id' => $company->id,
            //     'external_id' => $orderId,
            //     'status' => 'pending', // No food_service seria 'Novo Pedido'
            //     'source' => 'ifood',
            //     'description' => "Pedido vindo do iFood: #{$orderId}",
            //     'date' => now(),
            // ]);

            // Log de sucesso que o ERP "recebeu" e avisou
            Log::info("ERRO/AVISO: NOVO PEDIDO IFOOD RECEBIDO! ID: {$orderId}");

            // Dispara o Evento via WebSocket (Reverb) para avisar a dashboard visualmente
            NewIFoodOrderEvent::dispatch($company->id, [
                'id' => $orderId,
                'customer' => $this->payload['customer']['name'] ?? 'Cliente iFood',
                'total' => $this->payload['total']['value'] ?? 0,
                'status' => 'Novo Pedido',
                'source' => 'iFood'
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao salvar pedido iFood no banco: " . $e->getMessage());
        }
    }
}
