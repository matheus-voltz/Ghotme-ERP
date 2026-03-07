<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdemServico;
use App\Models\OrdemServicoPart;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function osStatus()
    {
        $companyId = auth()->user()->company_id;
        $stats = OrdemServico::where('company_id', $companyId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        return view('content.reports.os-status', compact('stats'));
    }

    public function consumedStock()
    {
        $companyId = auth()->user()->company_id;
        $mostUsedParts = OrdemServicoPart::select('inventory_item_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('ordemServico', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->whereHas('part') // Garante que o item ainda existe no estoque
            ->with('part')
            ->groupBy('inventory_item_id')
            ->orderBy('total_qty', 'desc')
            ->limit(15)
            ->get();
        return view('content.reports.consumed-stock', compact('mostUsedParts'));
    }

    /**
     * Relatório de Faturamento por OS (Mensal)
     */
    public function revenue()
    {
        $companyId = auth()->user()->company_id;
        // Fetch finalized OS with relations needed for calculation
        $osFinalizadas = OrdemServico::where('company_id', $companyId)
            ->with(['items', 'parts', 'client'])
            ->where('status', 'finalized')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate total via PHP Accessor (items + parts)
        $totalGeral = $osFinalizadas->sum('total');

        return view('content.reports.revenue', compact('osFinalizadas', 'totalGeral'));
    }

    /**
     * Relatório de Desempenho por Mecânico
     */
    public function mechanicPerformance()
    {
        $companyId = auth()->user()->company_id;
        $mechanics = User::where('company_id', $companyId)
            ->withCount(['ordensServico as total_os' => function ($query) use ($companyId) {
                $query->where('status', 'finalized')->where('company_id', $companyId);
            }])
            ->get()
            ->map(function ($user) use ($companyId) {
                $os = OrdemServico::where('user_id', $user->id)
                    ->where('company_id', $companyId)
                    ->where('status', 'finalized')
                    ->with(['items', 'parts'])
                    ->get();
                $user->revenue_generated = $os->sum->total;
                return $user;
            })
            ->sortByDesc('total_os');

        return view('content.reports.mechanic-performance', compact('mechanics'));
    }

    /**
     * Relatório de Tempo Médio por OS
     */
    public function averageTime()
    {
        $companyId = auth()->user()->company_id;
        $avgTime = OrdemServico::where('company_id', $companyId)
            ->where('status', 'finalized')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours'))
            ->first()->avg_hours;

        $osList = OrdemServico::where('company_id', $companyId)
            ->where('status', 'finalized')
            ->with(['client', 'veiculo'])
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        return view('content.reports.average-time', compact('avgTime', 'osList'));
    }

    /**
     * Relatório de Custo/Receita por Serviço
     */
    public function costPerService()
    {
        $companyId = auth()->user()->company_id;
        // Agrupa os itens de OS (finalizadas) por serviço
        $servicesReport = \App\Models\OrdemServicoItem::select(
            'service_id',
            DB::raw('count(*) as total_count'),
            DB::raw('sum(quantity) as total_qty'),
            DB::raw('sum(quantity * price) as total_revenue')
        )
            ->whereHas('ordemServico', function ($q) use ($companyId) {
                $q->where('status', 'finalized')->where('company_id', $companyId);
            })
            ->whereHas('service') // Garante que o serviço ainda existe
            ->with('service')
            ->groupBy('service_id')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return view('content.reports.cost-per-service', compact('servicesReport'));
    }

    /**
     * Relatório de Tempo Médio por Serviço (Individual)
     */
    public function averageTimePerService()
    {
        $companyId = auth()->user()->company_id;
        $servicesTime = \App\Models\OrdemServicoItem::select(
            'service_id',
            DB::raw('count(*) as total_executions'),
            DB::raw('AVG(TIMESTAMPDIFF(HOUR, ordem_servicos.created_at, ordem_servicos.updated_at)) as avg_hours')
        )
            ->join('ordem_servicos', 'ordem_servico_items.ordem_servico_id', '=', 'ordem_servicos.id')
            ->where('ordem_servicos.status', 'finalized')
            ->where('ordem_servicos.company_id', $companyId)
            ->whereHas('service')
            ->with('service')
            ->groupBy('service_id')
            ->orderBy('avg_hours', 'desc')
            ->get();

        return view('content.reports.average-time-per-service', compact('servicesTime'));
    }

    public function getOsStatusData()
    {
        $companyId = auth()->user()->company_id;
        $data = OrdemServico::where('company_id', $companyId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        return response()->json($data);
    }
}
