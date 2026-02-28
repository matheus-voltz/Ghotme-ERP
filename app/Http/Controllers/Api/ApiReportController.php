<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrdemServico;
use App\Models\Budget;
use App\Models\Clients;
use App\Models\User;
use Carbon\Carbon;

class ApiReportController extends Controller
{
    public function show(Request $request, $type)
    {
        $data = [];

        if ($type === 'revenue') {
            // Faturamento detalhado
            $monthlyRevenue = OrdemServico::whereIn('status', ['paid', 'finalized', 'completed'])
                ->whereMonth('updated_at', Carbon::now()->month)
                ->get()
                ->sum('total');

            $lastMonthRevenue = OrdemServico::whereIn('status', ['paid', 'finalized', 'completed'])
                ->whereMonth('updated_at', Carbon::now()->subMonth()->month)
                ->get()
                ->sum('total');

            $data = [
                'title' => 'Faturamento Mensal',
                'description' => 'Detalhes do Faturamento Atual vs Anterior.',
                'main_metric' => 'R$ ' . number_format($monthlyRevenue, 2, ',', '.'),
                'secondary_metric' => 'Mês anterior: R$ ' . number_format($lastMonthRevenue, 2, ',', '.'),
                'charts' => [
                    [
                        'label' => 'Total Faturado',
                        'value' => (float) $monthlyRevenue,
                        'color' => '#7367F0'
                    ],
                    [
                        'label' => 'Mês Passado',
                        'value' => (float) $lastMonthRevenue,
                        'color' => '#CECECE'
                    ]
                ],
                // Insights dinâmicos da IA
                'insights' => [
                    $monthlyRevenue >= $lastMonthRevenue
                        ? '✅ Seu faturamento cresceu! Continue com a boa retenção de clientes neste mês para manter a alta.'
                        : '⚠️ Atenção: O Faturamento está mais baixo. Que tal oferecer uma revisão preventiva ou descontos em peças paradas no estoque?',
                    '💡 Analise quais serviços geraram mais renda este mês e crie pacotes focados neles.'
                ]
            ];
        }

        if ($type === 'profitability') {
            // Lucratividade (Exemplo simplificado)
            $completedOS = OrdemServico::whereIn('status', ['paid', 'finalized', 'completed'])
                ->whereMonth('updated_at', Carbon::now()->month)
                ->get();

            $revenue = $completedOS->sum('total');
            $cost = 0; // Se tiver custo de peças, subtrair aqui
            foreach ($completedOS as $os) {
                foreach ($os->parts as $part) {
                    $cost += ($part->inventoryItem->cost_price ?? 0) * $part->quantity;
                }
            }

            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            $data = [
                'title' => 'Lucratividade (Margem Real)',
                'description' => 'Margem calculada debitando o custo unitário das peças usadas.',
                'main_metric' => number_format($margin, 1) . '%',
                'secondary_metric' => 'Lucro Bruto: R$ ' . number_format($profit, 2, ',', '.'),
                'charts' => [
                    [
                        'label' => 'Receita',
                        'value' => (float) $revenue,
                        'color' => '#28C76F'
                    ],
                    [
                        'label' => 'Custo Base',
                        'value' => (float) $cost,
                        'color' => '#EA5455'
                    ]
                ],
                'insights' => [
                    '💡 Evite grandes descontos na Mão de Obra, pois ela é a chave para sustentar serviços complexos.',
                    '🔧 As Peças representaram ' . ($revenue > 0 ? number_format(($cost / $revenue) * 100, 1) : 0) . '% dos custos unitários neste mês.',
                    '📈 Reveja seus fornecedores: pequenas quedas no preço das peças de ' . ($revenue > 0 ? number_format(($cost / $revenue) * 100, 1) : 0) . '% de custo aumentam seu Lucro final em cascata!'
                ]
            ];
        }

        if ($type === 'clients') {
            $totalClients = Clients::count();
            $newClientsThisMonth = Clients::whereMonth('created_at', Carbon::now()->month)->count();
            $activeClients = OrdemServico::whereMonth('created_at', '>=', Carbon::now()->subMonths(3))->distinct('client_id')->count('client_id');

            $data = [
                'title' => 'Desempenho de Clientes',
                'description' => 'Análise de aquisição e retenção da base de clientes.',
                'main_metric' => $totalClients . ' Cadastrados',
                'secondary_metric' => '+' . $newClientsThisMonth . ' novos clientes neste mês',
                'charts' => [
                    [
                        'label' => 'Ativos (3 meses)',
                        'value' => (float) $activeClients,
                        'color' => '#FF9F43'
                    ],
                    [
                        'label' => 'Inativos',
                        'value' => (float) ($totalClients - $activeClients),
                        'color' => '#CECECE'
                    ]
                ],
                'insights' => [
                    '🔥 A loja conquistou ' . $newClientsThisMonth . ' clientes novinhos em folha! Mande mensagens no WhatsApp de pós-venda.',
                    '🎯 Você sabia que um cliente retido custa 7x menos que um novo? Temos ' . ($totalClients - $activeClients) . ' clientes inativos no radar.',
                    '🤖 Sugiro disparar uma campanha via WhatsApp com desconto para consertos da Base Inativa nos próximos dias.'
                ]
            ];
        }

        if ($type === 'productivity') {
            // Produtividade
            $finalizedToday = OrdemServico::where('status', 'finalized')->whereDate('updated_at', Carbon::today())->count();
            $pending = OrdemServico::whereIn('status', ['pending', 'approved', 'running'])->count();

            $data = [
                'title' => 'Produtividade da Oficina',
                'description' => 'Acompanhamento do gargalo operacional da equipe técnica.',
                'main_metric' => $finalizedToday . ' Entregues hoje',
                'secondary_metric' => $pending . ' Veículos na fila ou em serviço',
                'charts' => [
                    [
                        'label' => 'Concluídas (Hoje)',
                        'value' => (float) $finalizedToday,
                        'color' => '#00CFE8'
                    ],
                    [
                        'label' => 'A Fazer (Pendente)',
                        'value' => (float) $pending,
                        'color' => '#FF9F43'
                    ]
                ],
                'insights' => [
                    '⚡ Gás total: Seus técnicos finalizaram ' . $finalizedToday . ' tarefas hoje.',
                    'Existem ' . $pending . ' Ordens de Serviço na fila ativa. Redistribua as pendentes caso alguma bancada esteja ociosa.',
                    '🤖 Atrasar entregas mancha a reputação rápido. Foque sua equipe nas OSs "Aprovadas" há mais tempo!'
                ]
            ];
        }

        if ($type === 'chart') {
            // 30 dias de receita
            $dailyData = [];
            $totalPeriod = 0;
            $bestDay = ['day' => '', 'value' => 0];
            $worstDay = ['day' => '', 'value' => PHP_INT_MAX];

            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $financialDaily = \App\Models\FinancialTransaction::where('type', 'in')
                    ->where('status', 'paid')
                    ->whereDate('paid_at', $date)
                    ->sum('amount');
                $osDaily = OrdemServico::with(['items', 'parts'])
                    ->whereIn('status', ['paid', 'finalized', 'completed'])
                    ->whereDate('updated_at', $date)
                    ->get()->sum('total');
                $dayTotal = (float)($financialDaily + $osDaily);

                $dailyData[] = [
                    'day' => $date->format('d/m'),
                    'weekday' => $date->translatedFormat('D'),
                    'value' => $dayTotal,
                ];
                $totalPeriod += $dayTotal;

                if ($dayTotal > $bestDay['value']) {
                    $bestDay = ['day' => $date->format('d/m'), 'value' => $dayTotal];
                }
                if ($dayTotal < $worstDay['value']) {
                    $worstDay = ['day' => $date->format('d/m'), 'value' => $dayTotal];
                }
            }

            $avgDaily = $totalPeriod / 30;

            // Top 5 serviços mais faturados no mês
            $topServices = \App\Models\OrdemServicoItem::join('services', 'ordem_servico_items.service_id', '=', 'services.id')
                ->whereHas('ordemServico', function ($q) {
                    $q->whereIn('status', ['paid', 'finalized', 'completed'])
                        ->whereMonth('updated_at', Carbon::now()->month);
                })
                ->selectRaw('services.name as service_name, SUM(ordem_servico_items.price * ordem_servico_items.quantity) as total_earned, COUNT(*) as qty')
                ->groupBy('services.name')
                ->orderByDesc('total_earned')
                ->limit(5)
                ->get()
                ->map(fn($item) => [
                    'name' => $item->service_name,
                    'earned' => (float) $item->total_earned,
                    'qty' => $item->qty,
                ]);

            $data = [
                'title' => 'Fluxo de Receita (30 dias)',
                'description' => 'Faturamento diário das últimas 4 semanas.',
                'main_metric' => 'R$ ' . number_format($totalPeriod, 2, ',', '.'),
                'secondary_metric' => 'Média diária: R$ ' . number_format($avgDaily, 2, ',', '.'),
                'chart_data' => $dailyData,
                'top_services' => $topServices,
                'highlights' => [
                    ['label' => '📈 Melhor dia', 'value' => $bestDay['day'] . ' — R$ ' . number_format($bestDay['value'], 2, ',', '.')],
                    ['label' => '📉 Pior dia', 'value' => $worstDay['day'] . ' — R$ ' . number_format($worstDay['value'], 2, ',', '.')],
                    ['label' => '📊 Média/dia', 'value' => 'R$ ' . number_format($avgDaily, 2, ',', '.')],
                ],
                'insights' => [
                    '🤖 Observe os padrões semanais: seus melhores dias costumam se repetir. Agende agendamentos estratégicos neles para maximizar a receita.',
                    '💡 Dias com faturamento zerado podem indicar oportunidade — considere promoções ou lembretes de manutenção preventiva.',
                    '🎯 Os 5 serviços que mais faturam são sua vitrine. Destaque-os em campanhas e no WhatsApp dos clientes!'
                ]
            ];
        }

        return response()->json($data);
    }
}
