<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiSupportService
{
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        // Usando o arquivo de config para suportar cache em produção
        $this->apiKey = config('services.ai.gemini_key');
        $this->model = config('services.ai.gemini_model', 'gemini-2.0-flash');
    }

    public function ask($messages, $userContext = [])
    {
        if (!$this->apiKey) {
            return "A chave GEMINI_API_KEY não foi encontrada no seu .env.";
        }

        $systemPrompt = $this->buildSystemPrompt($userContext);

        // O Gemini usa um formato de JSON diferente da OpenAI. 
        // Vamos converter o histórico de mensagens para o formato 'contents' do Gemini.
        $contents = [];
        
        // No Gemini 1.5, o System Prompt pode ser enviado como um campo separado ou 
        // como a primeira mensagem. Aqui usaremos o formato de histórico:
        foreach ($messages as $msg) {
            $contents[] = [
                'role' => ($msg['role'] === 'assistant' ? 'model' : 'user'),
                'parts' => [['text' => $msg['content']]]
            ];
        }

        // Endpoint da API do Gemini
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => $contents,
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 800,
            ]
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->successful()) {
                // O caminho da resposta no Gemini é candidates -> content -> parts -> text
                return $response->json('candidates.0.content.parts.0.text');
            }

            Log::error("Gemini API Error: " . $response->body());
            return "Desculpe, tive um problema técnico ao acessar o Gemini. Erro: " . ($response->json('error.message') ?? 'Desconhecido');
        } catch (\Exception $e) {
            Log::error("Gemini Exception: " . $e->getMessage());
            return "Houve um erro na conexão com meu cérebro digital.";
        }
    }

    protected function buildSystemPrompt($userContext)
    {
        $niche = $userContext['niche'] ?? 'automotive';
        
        return "Você é o 'Ghotme AI', o assistente técnico especializado no sistema ERP Ghotme.
        O sistema é focado em oficinas e prestadores de serviços, utilizando Laravel 12, Livewire e o template Vuexy.
        
        CONDIÇÕES ATUAIS DO USUÁRIO:
        - Nome: {$userContext['name']}
        - Empresa: {$userContext['company_name']}
        - Nicho do Sistema: {$niche} (Isso muda os termos: em 'automotive' usamos 'Veículos', em 'pet' usamos 'Pets').

        CAPACIDADES DO SISTEMA (Baseado no Código):
        1. Ordens de Serviço: Gerenciamento completo com checklists técnicos e fotos.
        2. Estoque: Controle de peças (InventoryItem) com SKU e preço de custo/venda.
        3. Marketplace: Integração com Mercado Livre (publicação automática).
        4. Financeiro: Gateways (Asaas, Mercado Pago, Stripe, PagBank) e controle de comissões.
        5. CRM: Gestão de Leads, Clientes e Veículos (ou Entidades conforme o nicho).
        6. Suporte: Chat interno e suporte direto via WhatsApp.

        DIRETRIZES DE RESPOSTA:
        - Seja útil, amigável e direto ao ponto.
        - NUNCA use hashtags (# ou ##) para títulos. Use negrito com asteriscos (**Título**) para destacar seções.
        - Se o usuário perguntar 'como faço X', explique o caminho no menu (ex: 'Vá em Oficina > Ordens de Serviço').
        - Use emojis moderadamente para ser amigável.
        - Responda SEMPRE em Português do Brasil.
        - Não invente funcionalidades que não existem no código acima.";
    }
}
