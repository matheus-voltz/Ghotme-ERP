<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrdemServico;
use App\Models\Budget;
use App\Models\Clients;
use App\Models\FinancialTransaction;
use App\Models\OrdemServicoItem;
use Carbon\Carbon;

class ApiReportController extends Controller
{
    public function show(Request $request, $type)
    {
        $user      = $request->user();
        $companyId = $user->company_id;
        $company   = $user->company;

        // O mobile envia ?niche= explicitamente — prioridade máxima
        // Fallback: company->niche, depois user->niche (legado), depois 'automotive'
        $niche = $request->query('niche')
            ?? $company?->niche
            ?? $user->niche
            ?? 'automotive';


        // Terminologia por nicho
        $terms = $this->getTerms($niche);

        $data = [];

        // ── FATURAMENTO ────────────────────────────────────────────────
        if ($type === 'revenue') {
            $monthlyRevenue = OrdemServico::where('company_id', $companyId)
                ->whereIn('status', ['paid', 'finalized', 'completed'])
                ->whereMonth('updated_at', Carbon::now()->month)
                ->whereYear('updated_at', Carbon::now()->year)
                ->get()->sum('total');

            $lastMonthRevenue = OrdemServico::where('company_id', $companyId)
                ->whereIn('status', ['paid', 'finalized', 'completed'])
                ->whereMonth('updated_at', Carbon::now()->subMonth()->month)
                ->whereYear('updated_at', Carbon::now()->subMonth()->year)
                ->get()->sum('total');

            $growth = $lastMonthRevenue > 0
                ? (($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
                : ($monthlyRevenue > 0 ? 100 : 0);
            $growthStr = ($growth >= 0 ? '+' : '') . number_format($growth, 1) . '%';

            $data = [
                'title'            => 'Faturamento Mensal',
                'description'      => "Receita bruta dos {$terms['pedidos']} finalizados neste mês.",
                'main_metric'      => 'R$ ' . number_format($monthlyRevenue, 2, ',', '.'),
                'secondary_metric' => "Mês anterior: R$ " . number_format($lastMonthRevenue, 2, ',', '.') . " ($growthStr)",
                'charts'           => [
                    ['label' => 'Este mês',    'value' => (float) $monthlyRevenue,    'color' => '#7367F0'],
                    ['label' => 'Mês passado', 'value' => (float) $lastMonthRevenue, 'color' => '#CECECE'],
                ],
                'insights' => $this->revenueInsights($niche, $monthlyRevenue, $lastMonthRevenue, $terms),
            ];
        }

        // ── LUCRATIVIDADE ──────────────────────────────────────────────
        if ($type === 'profitability') {
            $completedOS = OrdemServico::where('company_id', $companyId)
                ->whereIn('status', ['paid', 'finalized', 'completed'])
                ->whereMonth('updated_at', Carbon::now()->month)
                ->whereYear('updated_at', Carbon::now()->year)
                ->with(['parts.inventoryItem', 'items'])
                ->get();

            $revenue = $completedOS->sum('total');

            // Custo: peças/insumos (só onde faz sentido)
            $cost = 0;
            if ($niche !== 'food_service') {
                foreach ($completedOS as $os) {
                    foreach ($os->parts as $part) {
                        $cost += ($part->inventoryItem->cost_price ?? 0) * ($part->quantity ?? 1);
                    }
                }
            } else {
                // No food service calcula custo pelos items do inventário (insumos)
                foreach ($completedOS as $os) {
                    foreach ($os->parts as $part) {
                        $cost += ($part->inventoryItem->cost_price ?? 0) * ($part->quantity ?? 1);
                    }
                }
            }

            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
            $costPct = $revenue > 0 ? ($cost / $revenue) * 100 : 0;

            $data = [
                'title'            => 'Lucratividade (Margem Real)',
                'description'      => "Margem calculada deduzindo o custo dos {$terms['insumos']} utilizados.",
                'main_metric'      => number_format($margin, 1) . '%',
                'secondary_metric' => 'Lucro Bruto: R$ ' . number_format($profit, 2, ',', '.'),
                'charts'           => [
                    ['label' => 'Receita Bruta',         'value' => (float) $revenue, 'color' => '#28C76F'],
                    ['label' => "Custo {$terms['insumos']}", 'value' => (float) $cost, 'color' => '#EA5455'],
                ],
                'insights' => $this->profitabilityInsights($niche, $margin, $costPct, $revenue, $cost, $terms),
            ];
        }

        // ── CLIENTES ───────────────────────────────────────────────────
        if ($type === 'clients') {
            $totalClients        = Clients::where('company_id', $companyId)->count();
            $newClientsThisMonth = Clients::where('company_id', $companyId)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();
            $activeClients = OrdemServico::where('company_id', $companyId)
                ->where('created_at', '>=', Carbon::now()->subMonths(3))
                ->distinct('client_id')
                ->count('client_id');
            $inactiveClients = max(0, $totalClients - $activeClients);

            $data = [
                'title'            => "Desempenho de {$terms['clientes']}",
                'description'      => "Análise de aquisição e retenção de {$terms['clientes_lower']}.",
                'main_metric'      => "$totalClients Cadastrados",
                'secondary_metric' => "+$newClientsThisMonth novos {$terms['clientes_lower']} este mês",
                'charts'           => [
                    ['label' => 'Ativos (3 meses)', 'value' => (float) $activeClients,   'color' => '#FF9F43'],
                    ['label' => 'Inativos',          'value' => (float) $inactiveClients, 'color' => '#CECECE'],
                ],
                'insights' => $this->clientInsights($niche, $newClientsThisMonth, $inactiveClients, $totalClients, $terms),
            ];
        }

        // ── PRODUTIVIDADE ──────────────────────────────────────────────
        if ($type === 'productivity') {
            $finalizedToday = OrdemServico::where('company_id', $companyId)
                ->where('status', 'finalized')
                ->whereDate('updated_at', Carbon::today())
                ->count();
            $pending = OrdemServico::where('company_id', $companyId)
                ->whereIn('status', ['pending', 'approved', 'running'])
                ->count();
            $totalMonth = OrdemServico::where('company_id', $companyId)
                ->whereMonth('created_at', Carbon::now()->month)
                ->count();

            $data = [
                'title'            => "Produtividade — {$terms['estabelecimento']}",
                'description'      => "Acompanhamento do ritmo operacional da equipe.",
                'main_metric'      => "$finalizedToday {$terms['entregues']} hoje",
                'secondary_metric' => "$pending {$terms['pedidos']} na fila ou em preparo",
                'charts'           => [
                    ['label' => ucfirst($terms['entregues']) . ' hoje', 'value' => (float) $finalizedToday, 'color' => '#00CFE8'],
                    ['label' => 'Em andamento / Fila',  'value' => (float) $pending,        'color' => '#FF9F43'],
                ],
                'insights' => $this->productivityInsights($niche, $finalizedToday, $pending, $totalMonth, $terms),
            ];
        }

        // ── GRÁFICO DE RECEITA (30 dias) ───────────────────────────────
        if ($type === 'chart') {
            $dailyData   = [];
            $totalPeriod = 0;
            $bestDay     = ['day' => '', 'value' => 0];
            $worstDay    = ['day' => '', 'value' => PHP_INT_MAX];

            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);

                $financialDaily = FinancialTransaction::where('company_id', $companyId)
                    ->where('type', 'in')->where('status', 'paid')
                    ->whereDate('paid_at', $date)->sum('amount');

                $osDaily = OrdemServico::where('company_id', $companyId)
                    ->with(['items', 'parts'])
                    ->whereIn('status', ['paid', 'finalized', 'completed'])
                    ->whereDate('updated_at', $date)
                    ->get()->sum('total');

                $dayTotal  = (float) ($financialDaily + $osDaily);
                $dailyData[] = [
                    'day'     => $date->format('d/m'),
                    'weekday' => $date->translatedFormat('D'),
                    'value'   => $dayTotal,
                ];
                $totalPeriod += $dayTotal;

                if ($dayTotal > $bestDay['value'])   $bestDay  = ['day' => $date->format('d/m'), 'value' => $dayTotal];
                if ($dayTotal < $worstDay['value'])  $worstDay = ['day' => $date->format('d/m'), 'value' => $dayTotal];
            }

            $avgDaily = $totalPeriod / 30;

            // Top itens/serviços mais vendidos no nicho
            $topServices = OrdemServicoItem::join('services', 'ordem_servico_items.service_id', '=', 'services.id')
                ->whereHas('ordemServico', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                        ->whereIn('status', ['paid', 'finalized', 'completed'])
                        ->whereMonth('updated_at', Carbon::now()->month);
                })
                ->selectRaw('services.name as service_name, SUM(ordem_servico_items.price * ordem_servico_items.quantity) as total_earned, COUNT(*) as qty')
                ->groupBy('services.name')
                ->orderByDesc('total_earned')
                ->limit(5)->get()
                ->map(fn($item) => [
                    'name'   => $item->service_name,
                    'earned' => (float) $item->total_earned,
                    'qty'    => $item->qty,
                ]);

            $data = [
                'title'            => 'Fluxo de Receita (30 dias)',
                'description'      => 'Faturamento diário das últimas 4 semanas.',
                'main_metric'      => 'R$ ' . number_format($totalPeriod, 2, ',', '.'),
                'secondary_metric' => 'Média diária: R$ ' . number_format($avgDaily, 2, ',', '.'),
                'chart_data'       => $dailyData,
                'top_services'     => $topServices,
                'highlights'       => [
                    ['label' => '📈 Melhor dia', 'value' => $bestDay['day']  . ' — R$ ' . number_format($bestDay['value'], 2, ',', '.')],
                    ['label' => '📉 Pior dia',   'value' => $worstDay['day'] . ' — R$ ' . number_format($worstDay['value'], 2, ',', '.')],
                    ['label' => '📊 Média/dia',  'value' => 'R$ ' . number_format($avgDaily, 2, ',', '.')],
                ],
                'insights' => $this->chartInsights($niche, $avgDaily, $terms),
            ];
        }

        return response()->json($data);
    }

    // ── TERMINOLOGIA POR NICHO ─────────────────────────────────────────
    private function getTerms(string $niche): array
    {
        switch ($niche) {
            case 'food_service':
                return [
                    'pedidos'        => 'pedidos',
                    'pedido'         => 'pedido',
                    'insumos'        => 'insumos',
                    'clientes'       => 'Clientes',
                    'clientes_lower' => 'clientes',
                    'estabelecimento' => 'Cozinha',
                    'entregues'      => 'pedidos entregues',
                    'servico'        => 'item do cardápio',
                    'servicos'       => 'itens do cardápio',
                ];
            case 'pet':
                return [
                    'pedidos'        => 'atendimentos',
                    'pedido'         => 'atendimento',
                    'insumos'        => 'produtos',
                    'clientes'       => 'Tutores',
                    'clientes_lower' => 'tutores',
                    'estabelecimento' => 'Pet Shop',
                    'entregues'      => 'atendimentos concluídos',
                    'servico'        => 'serviço',
                    'servicos'       => 'serviços',
                ];
            case 'beauty_clinic':
                return [
                    'pedidos'        => 'atendimentos',
                    'pedido'         => 'atendimento',
                    'insumos'        => 'produtos',
                    'clientes'       => 'Clientes',
                    'clientes_lower' => 'clientes',
                    'estabelecimento' => 'Clínica',
                    'entregues'      => 'atendimentos finalizados',
                    'servico'        => 'procedimento',
                    'servicos'       => 'procedimentos',
                ];
            case 'electronics':
                return [
                    'pedidos'        => 'ordens de serviço',
                    'pedido'         => 'OS',
                    'insumos'        => 'peças',
                    'clientes'       => 'Clientes',
                    'clientes_lower' => 'clientes',
                    'estabelecimento' => 'Assistência',
                    'entregues'      => 'dispositivos entregues',
                    'servico'        => 'serviço',
                    'servicos'       => 'serviços',
                ];
            case 'construction':
                return [
                    'pedidos'        => 'ordens de serviço',
                    'pedido'         => 'OS',
                    'insumos'        => 'materiais',
                    'clientes'       => 'Clientes',
                    'clientes_lower' => 'clientes',
                    'estabelecimento' => 'Empresa',
                    'entregues'      => 'serviços entregues',
                    'servico'        => 'serviço',
                    'servicos'       => 'serviços',
                ];
            default: // automotive, workshop e qualquer outro
                return [
                    'pedidos'        => 'ordens de serviço',
                    'pedido'         => 'OS',
                    'insumos'        => 'peças',
                    'clientes'       => 'Clientes',
                    'clientes_lower' => 'clientes',
                    'estabelecimento' => 'Oficina',
                    'entregues'      => 'veículos entregues',
                    'servico'        => 'serviço',
                    'servicos'       => 'serviços',
                ];
        }
    }


    // ── INSIGHTS POR NICHO ─────────────────────────────────────────────
    private function revenueInsights(string $niche, float $current, float $last, array $terms): array
    {
        $isUp = $current >= $last;
        $base = $isUp
            ? "✅ Ótimo! O faturamento cresceu em relação ao mês passado. Continue atraindo {$terms['clientes_lower']} com a qualidade dos seus {$terms['servicos']}."
            : "⚠️ Atenção: O faturamento caiu comparado ao mês anterior. Que tal criar promoções ou combos para aquecer as vendas?";

        return match ($niche) {
            'food_service' => [
                $base,
                '💡 Analise quais itens do cardápio mais venderam e crie combos e promoções em torno deles.',
                '🛵 Pedidos de entrega (iFood/WhatsApp) tendem a aumentar a receita em até 30%. Já está aproveitando esse canal?',
            ],
            'pet' => [
                $base,
                '💡 Banho & tosa recorrente é a melhor fonte de renda estável de um pet shop. Fidelize tutores com pacotes mensais.',
                '🐾 Lembre os tutores via WhatsApp sobre vacinação e consultas periódicas — gera retorno garantido.',
            ],
            'beauty_clinic' => [
                $base,
                '💡 Procedimentos como limpeza de pele e design de sobrancelhas têm alta recorrência. Incentive o reagendamento na saída.',
                '💅 Clientes fidelizados gastam em média 3x mais. Crie um programa de pontos ou desconto na segunda visita.',
            ],
            default => [
                $base,
                '💡 Analise quais serviços geraram mais renda neste mês e crie pacotes focados neles.',
                '🔧 Revisões preventivas atraem clientes mesmo na baixa temporada. Dispare lembretes no WhatsApp!',
            ],
        };
    }

    private function profitabilityInsights(string $niche, float $margin, float $costPct, float $revenue, float $cost, array $terms): array
    {
        $marginStr = number_format($margin, 1);
        $costPctStr = number_format($costPct, 1);

        return match ($niche) {
            'food_service' => [
                $margin >= 60
                    ? "✅ Margem de {$marginStr}% — excelente para o ramo alimentício! A maioria das lanchonetes opera com 30-50%."
                    : "⚠️ Margem de {$marginStr}%. No food service, o ideal é acima de 50%. Reveja o custo dos insumos e o preço de venda.",
                '🧾 No food service, o maior impacto na margem vem do custo dos insumos. Negocie com fornecedores mensalmente.',
                '🍔 Itens com alta margem (bebidas, combos) são seus aliados. Destaque-os no cardápio para elevar o ticket médio.',
            ],
            'pet' => [
                $margin >= 50
                    ? "✅ Margem de {$marginStr}% — ótima saúde financeira para o pet shop!"
                    : "⚠️ Margem de {$marginStr}%. Revise o preço dos serviços e o custo dos produtos de higiene e veterinário.",
                '🐶 O custo de produtos representa {$costPctStr}% da receita. Trabalhe com fornecedores de confiança e compre em volume.',
                '💡 Serviços com maior margem (tosa, banho) devem ser priorizados nas promoções.',
            ],
            'beauty_clinic' => [
                $margin >= 55
                    ? "✅ Margem de {$marginStr}% — muito boa para o setor de beleza!"
                    : "⚠️ Margem de {$marginStr}%. Reveja o custo dos produtos e a precificação dos procedimentos.",
                '💄 Produtos representam {$costPctStr}% dos custos. Negocie com distribuidoras e compre em lote para reduzir o custo.',
                '💡 Procedimentos de maior valor agregado (como tratamentos faciais) têm margens melhores. Incentive o upsell!',
            ],
            'electronics' => [
                $margin >= 45
                    ? "✅ Margem de {$marginStr}% — saudável para assistência técnica!"
                    : "⚠️ Margem de {$marginStr}%. Peças representam {$costPctStr}% da receita. Reveja o markup de peças.",
                "🔧 As peças representam {$costPctStr}% dos custos unitários. Pequenas quedas no preço das peças aumentam o lucro em cascata!",
                '💡 Evite grandes descontos na mão de obra — ela é a chave para sustentar reparos complexos.',
            ],
            default => [ // auto
                $margin >= 40
                    ? "✅ Margem de {$marginStr}% — saudável para oficina mecânica!"
                    : "⚠️ Margem de {$marginStr}%. Reveja o custo de peças e a precificação de serviços.",
                "🔧 As peças representam {$costPctStr}% dos custos. Pequenas quedas no preço de peças aumentam o lucro em cascata!",
                '💡 Evite grandes descontos na Mão de Obra, pois ela é a chave para sustentar serviços complexos.',
            ],
        };
    }

    private function clientInsights(string $niche, int $newClients, int $inactiveClients, int $total, array $terms): array
    {
        return match ($niche) {
            'food_service' => [
                "🔥 {$newClients} novos clientes cadastrados neste mês! Já enviou uma mensagem de boas-vindas no WhatsApp?",
                "🛵 Clientes fidelizados pedem com mais frequência. Crie um programa de fidelidade (ex: 10 pedidos = 1 grátis).",
                "📲 Use grupos de WhatsApp ou Instagram para divulgar promoções do dia e atrair pedidos nas horas de menor movimento.",
            ],
            'pet' => [
                "🐾 {$newClients} novos tutores este mês! Mande uma mensagem de boas-vindas com dicas de cuidados dos pets.",
                "💡 Temos $inactiveClients tutores inativos. Que tal uma campanha com desconto no próximo banho ou consulta?",
                "📅 Vacinação e consultas periódicas são previsíveis. Agende lembretes automáticos para reativar tutores.",
            ],
            'beauty_clinic' => [
                "💅 {$newClients} novos clientes chegaram este mês — ótimo ritmo! Peça avaliações no Google para crescer ainda mais.",
                "💡 $inactiveClients clientes não retornaram há mais de 3 meses. Um SMS com desconto pode trazê-los de volta.",
                "📸 Antes e depois de procedimentos (com autorização) geram engajamento nas redes e atraem novos clientes.",
            ],
            default => [
                "🔥 {$newClients} novos {$terms['clientes_lower']} neste mês! Mande mensagens de pós-venda no WhatsApp.",
                "🎯 Temos $inactiveClients {$terms['clientes_lower']} inativos no radar. Um cliente retido custa 7x menos que um novo.",
                "🤖 Sugiro disparar uma campanha via WhatsApp com desconto para reativar a base inativa.",
            ],
        };
    }

    private function productivityInsights(string $niche, int $today, int $pending, int $totalMonth, array $terms): array
    {
        return match ($niche) {
            'food_service' => [
                "⚡ {$today} pedidos entregues hoje — excelente ritmo!",
                $pending > 0
                    ? "🔥 Ainda há $pending pedidos na fila. Verifique se a cozinha está com capacidade e evite atrasos."
                    : "✅ Fila limpa! Ótimo momento para preparar mise en place e antecipar os pedidos do próximo turno.",
                '🛵 Pedidos de entrega costumam ter prazo mais crítico. Priorize-os para manter a avaliação no iFood alta.',
            ],
            'pet' => [
                "⚡ $today atendimentos concluídos hoje — bom trabalho!",
                $pending > 0 ? "📋 $pending atendimentos ainda na fila. Comunique ao tutor o horário estimado de conclusão." : "✅ Sem pendências! Aproveite para organizar o estoque ou confirmar agendamentos de amanhã.",
                '🐾 Pontualidade nos atendimentos é fator decisivo na fidelização dos tutores.',
            ],
            'beauty_clinic' => [
                "⚡ $today procedimentos finalizados hoje!",
                $pending > 0 ? "💅 $pending clientes aguardando. Confirme com a equipe os horários e evite atrasos." : "✅ Agenda limpa! Bom momento para fazer follow-up com clientes de procedimentos recentes.",
                '📆 Ausências e cancelamentos de última hora afetam a produtividade. Implemente confirmação via WhatsApp no dia anterior.',
            ],
            default => [
                "⚡ $today {$terms['entregues']} hoje — ótima produtividade!",
                $pending > 0
                    ? "🔧 $pending {$terms['pedidos']} na fila ativa. Redistribua as pendentes se alguma bancada estiver ociosa."
                    : "✅ Fila zerada! Aproveite para revisar o estoque de peças ou adiantar orçamentos.",
                '🤖 Atrasar entregas mancha a reputação rápido. Foque nas ordens aprovadas há mais tempo.',
            ],
        };
    }

    private function chartInsights(string $niche, float $avgDaily, array $terms): array
    {
        return match ($niche) {
            'food_service' => [
                '🤖 Observe os horários de pico: finais de semana e almoço tendem a ter mais pedidos. Reforce a equipe nesses momentos.',
                '💡 Dias com faturamento baixo são oportunidade para promoções relâmpago no WhatsApp ou Instagram.',
                "🍔 Os itens que mais faturam são sua vitrine. Destaque-os no cardápio e nas fotos das redes sociais!",
            ],
            'pet' => [
                '🤖 Sábados costumam ser o dia de pico nos pet shops. Garanta equipe suficiente e insumos em estoque.',
                '💡 Dias com baixo faturamento podem indicar oportunidade para promoções de banho express.',
                '🐾 Os serviços mais faturados revelam o perfil dos seus tutores. Use esse dado para criar pacotes.',
            ],
            'beauty_clinic' => [
                '🤖 Os melhores dias da semana se repetem. Mantenha horários disponíveis nesses dias para maximizar o faturamento.',
                '💡 Dias com baixo movimento são ideais para treinamento da equipe ou promoções via Instagram.',
                '💅 Os procedimentos que mais geram receita são sua âncora — destaque-os nos stories e na vitrine.',
            ],
            default => [
                '🤖 Observe os padrões semanais: seus melhores dias costumam se repetir. Agende revisões preventivas estrategicamente.',
                '💡 Dias com faturamento zerado podem indicar oportunidade — considere promoções ou lembretes de manutenção.',
                "🎯 Os {$terms['servicos']} que mais faturam são sua vitrine. Destaque-os em campanhas e no WhatsApp!",
            ],
        };
    }
}
