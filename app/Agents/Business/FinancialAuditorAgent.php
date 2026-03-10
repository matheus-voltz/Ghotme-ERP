<?php

namespace App\Agents\Business;

use App\Agents\Core\BaseAgent;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Log;
use App\Models\SystemUpdate; // Using this existing model to log agent actions as a proof of concept

class FinancialAuditorAgent extends BaseAgent
{
    /**
     * Define the System Prompt for this specific agent.
     */
    public function getSystemPrompt(): string
    {
        return <<<PROMPT
Você é o "Auditor Financeiro IA" do Ghotme ERP.
Sua missão é analisar transações financeiras recém-criadas ou modificadas e identificar anomalias, 
inconsistências ou sugerir melhorias de fluxo de caixa para a empresa inquilina.

Retorne SEMPRE um JSON válido com a seguinte estrutura:
{
    "status": "ok|warning|critical",
    "observation": "Descrição do que você encontrou",
    "recommendation": "O que a empresa deve fazer"
}
Não inclua crases markdown (```json) ou qualquer outro texto fora do JSON.
PROMPT;
    }

    /**
     * Analyze a single FinancialTransaction context.
     * 
     * @param array $context Must contain 'transaction' object or array.
     */
    public function analyze(array $context)
    {
        if (!isset($context['transaction'])) {
            throw new \InvalidArgumentException("Context must contain 'transaction'.");
        }

        $tx = $context['transaction'];
        $amount = is_object($tx) ? $tx->amount : $tx['amount'];
        $type = is_object($tx) ? $tx->type : $tx['type'];
        $niche = is_object($tx) && $tx->company ? $tx->company->niche : 'desconhecido';

        $promptContext = sprintf(
            "Acabou de ocorrer uma transação de %s no valor de R$ %s. " .
                "A natureza da empresa é: %s. " .
                "Descrição da transação: %s. " .
                "Avaliando este contexto, existe algum risco evidente ou algo digno de nota?",
            strtoupper($type),
            number_format((float)$amount, 2, ',', '.'),
            $niche,
            is_object($tx) ? $tx->description : $tx['description']
        );

        Log::info("FinancialAuditorAgent is analyzing transaction ID: " . (is_object($tx) ? $tx->id : $tx['id']));

        $llmResponse = $this->callLLM($this->getSystemPrompt(), $promptContext);

        if ($llmResponse) {
            // Attempt to parse the JSON
            $json = json_decode($llmResponse, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
            Log::warning("FinancialAuditorAgent received non-JSON response from LLM.");
            return ['status' => 'error', 'observation' => 'LLM invalid format', 'recommendation' => 'N/A'];
        }

        return null;
    }

    /**
     * Act upon the analysis result.
     * For example: save an alert in the database or send an email if status is critical.
     */
    public function act($analysisResult): bool
    {
        if (!$analysisResult || !isset($analysisResult['status'])) {
            return false;
        }

        // Just as an example, if it's warning or critical, we log it to SystemUpdate/Changelog for visibility
        if (in_array($analysisResult['status'], ['warning', 'critical'])) {

            // In a real scenario, you might have an `AiInsights` or `Alerts` specific table.
            SystemUpdate::create([
                'title' => 'Alerta Financeiro (IA Auto-Auditoria)',
                'description' => $analysisResult['observation'] . " | Sugestão: " . $analysisResult['recommendation'],
                'type' => 'bug', // Repurposing the type just to highlight
                'is_active' => true,
            ]);

            Log::channel('single')->info("Agent Acted! Created a SystemUpdate for a critical transaction.");
            return true;
        }

        return true; // OK status processed successfully but didn't require action
    }
}
