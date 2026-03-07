<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\OrdemServicoPart;
use App\Models\FinancialTransaction;
use App\Models\Appointment;
use App\Models\AiBusinessMemory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AiToolsService
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Salva um fato importante sobre o negócio para memória de longo prazo.
     */
    public function saveBusinessFact($fact, $key = 'general_fact', $importance = 1)
    {
        try {
            // Se já existir um fato com a mesma chave (ex: 'focus_of_month'), atualiza em vez de criar novo
            AiBusinessMemory::updateOrCreate(
                ['company_id' => $this->companyId, 'key' => $key],
                ['content' => $fact, 'importance' => $importance]
            );

            return "Entendido! Memorizei este fato sobre o seu negócio para nossas próximas conversas.";
        } catch (\Exception $e) {
            return "Erro ao tentar salvar fato: " . $e->getMessage();
        }
    }

    /**
     * Identifica itens com baixa rotatividade (sem vendas nos últimos X dias)
     */
    public function getLowTurnoverItems($days = 30)
    {
        // Busca itens que não aparecem em OrdemServicoPart (vendas de peças) 
        // ou que o estoque está parado há muito tempo
        $soldPartsIds = OrdemServicoPart::where('created_at', '>=', now()->subDays($days))
            ->pluck('inventory_item_id')
            ->unique();

        $items = InventoryItem::where('company_id', $this->companyId)
            ->whereNotIn('id', $soldPartsIds)
            ->where('quantity', '>', 0)
            ->select('id', 'name', 'sku', 'quantity', 'cost_price', 'selling_price', 'updated_at')
            ->orderBy('quantity', 'desc')
            ->take(15)
            ->get();

        if ($items->isEmpty()) {
            return "Não encontrei itens sem movimentação nos últimos {$days} dias.";
        }

        return $items->toArray();
    }

    /**
     * Busca itens com estoque baixo (abaixo da quantidade mínima)
     */
    public function getLowStockItems()
    {
        $items = InventoryItem::where('company_id', $this->companyId)
            ->when(get_current_niche() === 'food_service', function($q) {
                return $q->where('is_ingredient', true);
            })
            ->whereRaw('quantity <= min_quantity')
            ->where('is_active', true)
            ->select('name', 'sku', 'quantity', 'min_quantity')
            ->get();

        if ($items->isEmpty()) {
            return "O estoque está saudável. Nenhum item abaixo do mínimo.";
        }

        return $items->toArray();
    }

    /**
     * Lista os clientes que mais gastaram nos últimos X dias.
     */
    public function getTopCustomers($days = 30)
    {
        $topCustomers = FinancialTransaction::where('company_id', $this->companyId)
            ->where('type', 'in')
            ->where('status', 'paid')
            ->where('paid_at', '>=', now()->subDays($days))
            ->whereNotNull('client_id')
            ->with('client')
            ->selectRaw('client_id, SUM(amount) as total_spent')
            ->groupBy('client_id')
            ->orderByDesc('total_spent')
            ->take(5)
            ->get();

        if ($topCustomers->isEmpty()) {
            return "Nenhuma transação paga registrada com clientes vinculados nos últimos {$days} dias.";
        }

        $result = [];
        foreach ($topCustomers as $transaction) {
            $result[] = [
                'client_name' => $transaction->client->name ?? 'Cliente Desconhecido',
                'total_spent' => (float) $transaction->total_spent,
                'phone' => $transaction->client->phone ?? 'Não informado'
            ];
        }

        return $result;
    }

    /**
     * Identifica clientes com pagamentos em atraso (inadimplentes).
     */
    public function getOverdueTransactions()
    {
        $overdue = FinancialTransaction::where('company_id', $this->companyId)
            ->where('type', 'in')
            ->where('status', 'pending')
            ->where('due_date', '<', now()->startOfDay())
            ->whereNotNull('client_id')
            ->with('client')
            ->select('client_id', 'amount', 'due_date', 'description')
            ->orderBy('due_date', 'asc')
            ->take(10)
            ->get();

        if ($overdue->isEmpty()) {
            return "Boas notícias! Não encontrei nenhuma conta a receber em atraso no momento.";
        }

        $result = [];
        foreach ($overdue as $transaction) {
            $result[] = [
                'client_name' => $transaction->client->name ?? 'Cliente Desconhecido',
                'amount' => (float) $transaction->amount,
                'days_late' => Carbon::parse($transaction->due_date)->diffInDays(now()),
                'description' => $transaction->description,
                'phone' => $transaction->client->phone ?? 'Não informado'
            ];
        }

        return $result;
    }

    /**
     * Busca os compromissos agendados para hoje ou amanhã.
     */
    public function getAppointments($day = 'today')
    {
        $date = $day === 'tomorrow' ? now()->addDay()->toDateString() : now()->toDateString();
        
        $appointments = Appointment::where('company_id', $this->companyId)
            ->whereDate('start_datetime', $date)
            ->with(['client', 'service'])
            ->orderBy('start_datetime', 'asc')
            ->get();

        if ($appointments->isEmpty()) {
            $dayText = $day === 'tomorrow' ? 'amanhã' : 'hoje';
            return "Não há agendamentos marcados para {$dayText}.";
        }

        $result = [];
        foreach ($appointments as $appt) {
            $result[] = [
                'time' => Carbon::parse($appt->start_datetime)->format('H:i'),
                'client_name' => $appt->client->name ?? 'Cliente Avulso',
                'service_name' => $appt->service->name ?? 'Serviço Geral',
                'status' => $appt->status
            ];
        }

        return $result;
    }

    /**
     * Analisa as vendas (Serviços e Produtos) para identificar o que mais traz receita.
     */
    public function getSalesAnalytics($days = 30)
    {
        $topServices = \App\Models\OrdemServicoItem::whereHas('ordemServico', function($q) {
                $q->where('company_id', $this->companyId)->where('status', 'finished');
            })
            ->where('created_at', '>=', now()->subDays($days))
            ->with('service')
            ->selectRaw('service_id, COUNT(*) as qty, SUM(price * quantity) as total_revenue')
            ->groupBy('service_id')
            ->orderByDesc('total_revenue')
            ->take(10)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->service->name ?? 'Serviço s/ Nome',
                    'vendas' => $item->qty,
                    'receita' => (float) $item->total_revenue
                ];
            });

        $topProducts = \App\Models\OrdemServicoPart::whereHas('ordemServico', function($q) {
                $q->where('company_id', $this->companyId)->where('status', 'finished');
            })
            ->where('created_at', '>=', now()->subDays($days))
            ->with('inventoryItem')
            ->selectRaw('inventory_item_id, COUNT(*) as qty, SUM(price * quantity) as total_revenue')
            ->groupBy('inventory_item_id')
            ->orderByDesc('total_revenue')
            ->take(10)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->inventoryItem->name ?? 'Peça s/ Nome',
                    'vendas' => $item->qty,
                    'receita' => (float) $item->total_revenue
                ];
            });

        return [
            'periodo_dias' => $days,
            'top_servicos' => $topServices,
            'top_produtos' => $topProducts
        ];
    }

    /**
     * Definições das ferramentas para enviar ao Gemini
     */
    public static function getToolDefinitions()
    {
        return [
            [
                'function_declarations' => [
                    [
                        'name' => 'get_low_turnover_items',
                        'description' => 'Identifica itens de estoque com baixa rotatividade (sem vendas/uso recente).',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'days' => [
                                    'type' => 'integer',
                                    'description' => 'Número de dias para considerar como baixa rotatividade (padrão 30).'
                                ]
                            ]
                        ]
                    ],
                    [
                        'name' => 'get_low_stock_items',
                        'description' => 'Lista itens que estão com estoque abaixo do nível mínimo configurado.',
                    ],
                    [
                        'name' => 'get_top_customers',
                        'description' => 'Lista os clientes que mais gastaram dinheiro na empresa em um determinado período (ranking VIP).',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'days' => [
                                    'type' => 'integer',
                                    'description' => 'Período em dias para analisar (padrão 30).'
                                ]
                            ]
                        ]
                    ],
                    [
                        'name' => 'get_overdue_transactions',
                        'description' => 'Busca a lista de clientes inadimplentes, ou seja, que possuem contas a receber vencidas e não pagas.',
                    ],
                    [
                        'name' => 'get_appointments',
                        'description' => 'Consulta a agenda da empresa para ver os compromissos marcados para hoje ou amanhã.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'day' => [
                                    'type' => 'string',
                                    'description' => 'Qual dia verificar. Valores permitidos: "today" (hoje) ou "tomorrow" (amanhã).'
                                ]
                            ]
                        ]
                    ],
                    [
                        'name' => 'get_sales_analytics',
                        'description' => 'Analisa o faturamento real (Serviços e Peças vendidos) nos últimos X dias para identificar oportunidades de lucro e os itens mais rentáveis.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'days' => [
                                    'type' => 'integer',
                                    'description' => 'Período para análise em dias (padrão 30).'
                                ]
                            ]
                        ]
                    ],
                    [
                        'name' => 'save_business_fact',
                        'description' => 'Salva um fato importante sobre o negócio do cliente para lembrar em conversas futuras (metas, preferências, avisos).',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'fact' => [
                                    'type' => 'string',
                                    'description' => 'O fato ou informação relevante a ser lembrada. Ex: "O dono quer focar em pneus Michelin este mês."'
                                ],
                                'key' => [
                                    'type' => 'string',
                                    'description' => 'Uma chave curta para agrupar o fato (Ex: meta_mes, preferencia_dono, aviso_estoque).'
                                ],
                                'importance' => [
                                    'type' => 'integer',
                                    'description' => 'Nível de importância de 1 a 5 (padrão 1).'
                                ]
                            ],
                            'required' => ['fact']
                        ]
                    ]
                ]
            ]
        ];
    }
}
