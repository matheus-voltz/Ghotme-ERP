<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfitabilityReportController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;

        // Ordens Finalizadas nos últimos 30 dias
        $recentOrders = OrdemServico::where('company_id', $companyId)
            ->whereIn('status', ['completed', 'finalized'])
            ->where('updated_at', '>=', now()->subDays(30))
            ->with(['items', 'parts.part'])
            ->get();

        $totalRevenue = $recentOrders->sum('total');
        $totalCost = $recentOrders->sum('parts_cost_total');
        $totalProfit = $totalRevenue - $totalCost;
        $avgMargin = $recentOrders->avg('profit_margin') ?? 0;

        // Top Serviços por Lucro (Simulado via agrupamento de nomes)
        $servicesPerformance = DB::table('ordem_servico_items')
            ->join('ordem_servicos', 'ordem_servico_items.ordem_servico_id', '=', 'ordem_servicos.id')
            ->join('services', 'ordem_servico_items.service_id', '=', 'services.id')
            ->where('ordem_servicos.company_id', $companyId)
            ->whereIn('ordem_servicos.status', ['completed', 'finalized'])
            ->select('services.name', 
                     DB::raw('SUM(ordem_servico_items.price * ordem_servico_items.quantity) as revenue'),
                     DB::raw('SUM(ordem_servico_items.duration_seconds) as total_time'))
            ->groupBy('services.id', 'services.name')
            ->orderBy('revenue', 'desc')
            ->limit(5)
            ->get();

        return view('content.reports.profitability', compact(
            'totalRevenue', 
            'totalCost', 
            'totalProfit', 
            'avgMargin', 
            'recentOrders',
            'servicesPerformance'
        ));
    }
}
