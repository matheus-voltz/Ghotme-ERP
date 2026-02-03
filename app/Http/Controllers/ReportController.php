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
        $monthlyRevenue = OrdemServico::select(
            DB::raw('SUM(total_amount) as total'), // Assumindo que podemos calcular ou temos um campo
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month")
        )
        ->where('status', 'finalized')
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->get();

        // Como a soma do total é um accessor, vamos calcular via coleção para ser preciso
        $osFinalizadas = OrdemServico::with(['items', 'parts'])->where('status', 'finalized')->get();
        $totalGeral = $osFinalizadas->sum->total;

        return view('content.reports.revenue', compact('osFinalizadas', 'totalGeral'));
    }

    /**
     * Relatório de Desempenho por Mecânico
     */
    public function mechanicPerformance()
    {
        $mechanics = User::withCount(['ordensServico as total_os' => function($query) {
            $query->where('status', 'finalized');
        }])
        ->get()
        ->map(function($user) {
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

    public function getOsStatusData()
    {
        $data = OrdemServico::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        return response()->json($data);
    }
}
