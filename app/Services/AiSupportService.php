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
        O sistema é multi-nicho e adapta suas funcionalidades conforme o ramo do cliente.

        CONDIÇÕES ATUAIS DO USUÁRIO:
        - Nome: {$userContext['name']}
        - Empresa: {$userContext['company_name']}
        - Nicho Ativo: {$niche}

        REGRAS DE CONTEXTO POR NICHO:
        1. Se o nicho for 'food_service': Use termos como 'Pedidos', 'Comandas', 'Cozinha', 'Lanches' e 'Ingredientes'. Fale sobre hot-dogs, pães e molhos.
        2. Se o nicho for 'automotive' ou 'workshop': Use 'Ordens de Serviço', 'Veículos', 'Placas' e 'Peças'.
        3. Se o nicho for 'electronics': Use 'Dispositivos', 'Bancada' e 'Conserto'.

        DIRETRIZES DE RESPOSTA:
        - Responda SEMPRE baseado no nicho ativo ({$niche}). Nunca ofereça funções de mecânica para um dono de lanchonete.
        - Seja útil, amigável e direto ao ponto.
        - NUNCA use hashtags (# ou ##) para títulos. Use negrito com asteriscos (**Texto**) para destacar seções.
        - Responda SEMPRE em Português do Brasil.";
    }
}
