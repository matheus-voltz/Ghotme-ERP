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
        $stats = OrdemServico::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        return view('content.reports.os-status', compact('stats'));
    }

    public function consumedStock()
    {
        $mostUsedParts = OrdemServicoPart::select('inventory_item_id', DB::raw('SUM(quantity) as total_qty'))
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
        // Fetch finalized OS with relations needed for calculation
        $osFinalizadas = OrdemServico::with(['items', 'parts'])
            ->where('status', 'finalized')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate total via PHP Accessor (items + parts)
        $totalGeral = $osFinalizadas->sum('total');

        // Optional: Group by Month if needed for charts later
        // $monthlyRevenue = $osFinalizadas->groupBy(fn($os) => $os->created_at->format('Y-m'))
        //    ->map(fn($group) => $group->sum('total'));

        return view('content.reports.revenue', compact('osFinalizadas', 'totalGeral'));
    }

    /**
     * Relatório de Desempenho por Mecânico
     */
    public function mechanicPerformance()
    {
        $mechanics = User::withCount(['ordensServico as total_os' => function ($query) {
            $query->where('status', 'finalized');
        }])
            ->get()
            ->map(function ($user) {
                $os = OrdemServico::where('user_id', $user->id)->where('status', 'finalized')->with(['items', 'parts'])->get();
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
        $avgTime = OrdemServico::where('status', 'finalized')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours'))
            ->first()->avg_hours;

        $osList = OrdemServico::where('status', 'finalized')
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
        // Agrupa os itens de OS (finalizadas) por serviço
        // Calcula quantas vezes foi usado e o valor total gerado
        $servicesReport = \App\Models\OrdemServicoItem::select(
            'service_id',
            DB::raw('count(*) as total_count'),
            DB::raw('sum(quantity) as total_qty'),
            DB::raw('sum(quantity * price) as total_revenue')
        )
            ->whereHas('ordemServico', function ($q) {
                $q->where('status', 'finalized');
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
        // Precisamos calcular o tempo médio que cada TIPO de serviço leva
        // Como o tempo é registrado na OS inteira, faremos uma estimativa baseada nas OS que contém esse serviço
        // Ou, se houver um campo de tempo no item, melhor ainda. 
        // Assumindo que o tempo é da OS:

        $servicesTime = \App\Models\OrdemServicoItem::select(
            'service_id',
            DB::raw('count(*) as total_executions'),
            DB::raw('AVG(TIMESTAMPDIFF(HOUR, ordem_servicos.created_at, ordem_servicos.updated_at)) as avg_hours')
        )
            ->join('ordem_servicos', 'ordem_servico_items.ordem_servico_id', '=', 'ordem_servicos.id')
            ->where('ordem_servicos.status', 'finalized')
            ->whereHas('service')
            ->with('service')
            ->groupBy('service_id')
            ->orderBy('avg_hours', 'desc')
            ->get();

        return view('content.reports.average-time-per-service', compact('servicesTime'));
    }

    public function getOsStatusData()
    {
        $data = OrdemServico::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        return response()->json($data);
    }
}
