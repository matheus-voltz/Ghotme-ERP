<?php

namespace App\Agents\Business;

use App\Agents\Core\BaseAgent;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\Log;
use App\Models\SystemUpdate;

class InventoryOptimizationAgent extends BaseAgent
{
    public function getSystemPrompt(): string
    {
        return <<<PROMPT
Você é o "Estrategista de Estoque IA" do Ghotme ERP.
Sua missão é analisar o estado atual do inventário de uma empresa e fornecer insights críticos sobre reposição, validade de insumos e otimização de capital.

Retorne SEMPRE um JSON válido:
{
    "status": "ok|warning|critical",
    "observation": "Descrição do achado técnico",
    "recommendation": "Ação imediata recomendada"
}
PROMPT;
    }

    public function analyze(array $context)
    {
        if (!isset($context['item'])) {
            return null;
        }

        $item = $context['item'];
        $quantity = $item->quantity;
        $minQuantity = $item->min_quantity;
        $expiryDate = $item->expiry_date;

        $promptContext = sprintf(
            "Produto: %s. Estoque Atual: %s %s. Estoque Mínimo Sugerido: %s %s. " .
                "Data de Validade: %s. " .
                "Analise se este item precisa de reposição urgente, se há risco de perda por validade ou se o nível está saudável.",
            $item->name,
            $quantity,
            $item->unit,
            $minQuantity,
            $item->unit,
            $expiryDate ? $expiryDate->format('d/m/Y') : 'N/A'
        );

        $llmResponse = $this->callLLM($this->getSystemPrompt(), $promptContext);

        if ($llmResponse) {
            $json = json_decode($llmResponse, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        return null;
    }

    public function act($analysisResult): bool
    {
        if (!$analysisResult || !isset($analysisResult['status'])) {
            return false;
        }

        if (in_array($analysisResult['status'], ['warning', 'critical'])) {
            $item = $this->context['item'];

            \App\Models\AiInsight::create([
                'company_id' => $item->company_id,
                'agent_name' => 'InventoryOptimizationAgent',
                'title' => 'IA: Otimização de Estoque',
                'observation' => $analysisResult['observation'],
                'recommendation' => $analysisResult['recommendation'],
                'status' => $analysisResult['status'],
            ]);

            Log::info("InventoryOptimizationAgent generated a system alert.");
            return true;
        }

        return true;
    }
}
