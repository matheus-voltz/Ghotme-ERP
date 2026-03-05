<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\AiConsultantChat;
use App\Models\FinancialTransaction;
use App\Models\OrdemServico;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessAiConsultantMessage implements ShouldQueue
{
    use Queueable;

    public $chatId;
    public $userMessage;
    public $user;
    public $company;

    /**
     * Create a new job instance.
     */
    public function __construct($chatId, $userMessage, $user, $company)
    {
        $this->chatId = $chatId;
        $this->userMessage = $userMessage;
        $this->user = $user;
        $this->company = $company;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $chat = AiConsultantChat::findOrFail($this->chatId);
            $companyId = $this->company->id;

            // Busca contexto do negócio
            $nicheKey = get_current_niche($this->company);
            $nichesNames = [
                'workshop' => 'Oficina Mecânica',
                'automotive' => 'Centro Automotivo',
                'electronics' => 'Assistência Técnica de Eletrônicos',
                'pet' => 'Pet Shop e Clínica Veterinária',
                'beauty_clinic' => 'Clínica de Estética',
                'food_service' => 'Food Service / Restaurante / Lanchonete / Balcão',
                'construction' => 'Construtora e Empreiteira'
            ];
            $nicheName = $nichesNames[$nicheKey] ?? $nicheKey;

            $revenue = FinancialTransaction::where('company_id', $companyId)->where('type', 'in')->where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('amount');
            $expenses = FinancialTransaction::where('company_id', $companyId)->where('type', 'out')->where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('amount');
            
            // Detalhamento para sugestão de cortes
            $expensesByCategory = FinancialTransaction::where('company_id', $companyId)
                ->where('type', 'out')
                ->where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->orderByDesc('total')
                ->get();

            $topSuppliers = FinancialTransaction::where('company_id', $companyId)
                ->where('type', 'out')
                ->where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->with('supplier')
                ->selectRaw('supplier_id, SUM(amount) as total')
                ->groupBy('supplier_id')
                ->orderByDesc('total')
                ->take(5)
                ->get();

            $pendingOS = OrdemServico::where('company_id', $companyId)->where('status', 'pending')->count();

            // Memória de Longo Prazo do Tenant
            $businessMemories = \App\Models\AiBusinessMemory::getContextForCompany($companyId);
            $memorySection = !empty($businessMemories) 
                ? "**MEMÓRIA DO NEGÓCIO (Fatos anteriores):**\n{$businessMemories}\n" 
                : "";

            $companyName = $this->company->name ?? 'sua empresa';


            // Base de conhecimento minimalista injetada no banco/job para não poluir o controller
            $systemKnowledge = view('content.pages.ai-consultant.system_manual')->render();

            $expenseDetails = "";
            foreach ($expensesByCategory as $item) {
                $categoryName = $item->category ?: 'Outros/Não Categorizado';
                $expenseDetails .= "- {$categoryName}: R$ " . number_format($item->total, 2, ',', '.') . "\n";
            }

            $supplierDetails = "";
            foreach ($topSuppliers as $item) {
                $supplierName = $item->supplier->name ?? 'Fornecedor Desconhecido';
                $supplierDetails .= "- {$supplierName}: R$ " . number_format($item->total, 2, ',', '.') . "\n";
            }

            $systemPrompt = "Você é o 'Ghotme Advisor' — Consultor Estratégico e Especialista em Suporte Nível 1 do sistema Ghotme ERP, especializado no nicho de {$nicheName}.

Sua dupla missão é:
1. **Consultor de Negócios:** Analise os dados reais da empresa e sugira ações concretas para aumentar a receita, reduzir custos e otimizar processos.
2. **Especialista no Sistema:** Oriente com precisão sobre como usar qualquer funcionalidade do Ghotme ERP.

// DADOS EM TEMPO REAL DA EMPRESA (Mês atual):
- 🏢 Empresa: {$companyName}
- 💰 Receita Total: R$ " . number_format($revenue, 2, ',', '.') . "
- 💸 Despesas Totais: R$ " . number_format($expenses, 2, ',', '.') . "

**DETALHAMENTO DE DESPESAS POR CATEGORIA:**
{$expenseDetails}

**TOP 5 FORNECEDORES (MAIOR GASTO):**
{$supplierDetails}

- 📋 " . ($nicheKey === 'food_service' ? 'Pedidos Pendentes (Cozinha/Balcão)' : 'OS Pendentes') . ": {$pendingOS}
- 🏭 Nicho: {$nicheName}

{$memorySection}

{$systemKnowledge}

**REGRAS DE COMPORTAMENTO:**
- Responda sempre em Português do Brasil.
- Use Markdown para formatar.
- **PROIBIDO RESPOSTAS GENÉRICAS:** Nunca dê conselhos teóricos sem antes consultar os dados do cliente usando as ferramentas disponíveis (Tools).
- **TOOL-FIRST:** Antes de sugerir uma estratégia, use ferramentas como 'get_sales_analytics' para ver o que já está sendo vendido ou 'get_low_turnover_items' para ver o que está parado no estoque.
- Se o usuário disser algo importante sobre suas preferências ou metas, use a ferramenta 'save_business_fact' para salvar esse conhecimento para sempre.
- Nunca invente preços ou URLs fictícios.";

            // Prepara histórico para o Gemini
            $history = $chat->messages()->orderBy('created_at', 'asc')->get()->map(function ($msg) {
                return [
                    'role' => $msg->role === 'user' ? 'user' : 'model',
                    'parts' => [['text' => $msg->content]]
                ];
            })->toArray();

            $apiKey = env('GEMINI_API_KEY');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

            $aiToolsService = new \App\Services\AiToolsService($companyId);
            $toolDefinitions = \App\Services\AiToolsService::getToolDefinitions();

            // Loop de execução (máximo 5 iterações de ferramentas para evitar loops infinitos)
            $maxIterations = 5;
            $currentIteration = 0;
            $toolCallOccurred = false;

            while ($currentIteration < $maxIterations) {
                $payload = [
                    'system_instruction' => [
                        'parts' => [['text' => $systemPrompt]]
                    ],
                    'contents' => $history,
                    'tools' => $toolDefinitions,
                    'tool_config' => [
                        'function_calling_config' => [
                            'mode' => 'AUTO'
                        ]
                    ]
                ];

                $response = Http::timeout(45)->post($url, $payload);

                if (!$response->successful()) {
                    Log::error('Erro na API Gemini: ' . $response->body());
                    return;
                }

                $candidate = $response->json('candidates.0');
                $message = $candidate['content'];
                $parts = $message['parts'];
                
                // Adiciona a resposta da IA ao histórico interno para a próxima rodada
                $history[] = $message;

                $hasFunctionCall = false;
                foreach ($parts as $part) {
                    if (isset($part['functionCall'])) {
                        $hasFunctionCall = true;
                        $toolCallOccurred = true;
                        $functionName = $part['functionCall']['name'];
                        $args = $part['functionCall']['args'] ?? [];

                        // Executa a ferramenta localmente
                        $result = null;
                        if ($functionName === 'get_low_turnover_items') {
                            $result = $aiToolsService->getLowTurnoverItems($args['days'] ?? 30);
                        } elseif ($functionName === 'get_low_stock_items') {
                            $result = $aiToolsService->getLowStockItems();
                        } elseif ($functionName === 'get_top_customers') {
                            $result = $aiToolsService->getTopCustomers($args['days'] ?? 30);
                        } elseif ($functionName === 'get_overdue_transactions') {
                            $result = $aiToolsService->getOverdueTransactions();
                        } elseif ($functionName === 'get_appointments') {
                            $result = $aiToolsService->getAppointments($args['day'] ?? 'today');
                        } elseif ($functionName === 'save_business_fact') {
                            $result = $aiToolsService->saveBusinessFact($args['fact'], $args['key'] ?? 'general_fact', $args['importance'] ?? 1);
                        } else {
                            $result = "Função não encontrada.";
                        }

                        // Adiciona o resultado da função ao histórico (role: function)
                        $history[] = [
                            'role' => 'function', // Para o Gemini 1.5/2.0 o role da resposta de função é 'function' ou via content parts
                            'parts' => [
                                [
                                    'functionResponse' => [
                                        'name' => $functionName,
                                        'response' => [
                                            'name' => $functionName,
                                            'content' => $result
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    }
                }

                if (!$hasFunctionCall) {
                    // Se não houver mais chamadas de função, o texto final está aqui
                    $aiText = '';
                    foreach ($parts as $part) {
                        if (isset($part['text'])) {
                            $aiText .= $part['text'];
                        }
                    }

                    if (!empty($aiText)) {
                        $chat->messages()->create([
                            'role' => 'assistant',
                            'content' => trim($aiText)
                        ]);
                        $chat->touch();
                    }
                    break;
                }

                $currentIteration++;
            }

        } catch (\Exception $e) {
            Log::error('Exceção Job AiConsultant: ' . $e->getMessage());
        }
    }
}
