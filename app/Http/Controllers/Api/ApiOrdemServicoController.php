<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use App\Models\Budget;
use App\Models\Clients;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\FinancialTransaction;

class ApiOrdemServicoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = OrdemServico::with(['client', 'veiculo', 'user'])->latest();
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        return response()->json($query->paginate(50));
    }

    public function show($id)
    {
        $os = OrdemServico::with(['client', 'veiculo', 'user', 'items.service', 'parts.inventoryItem'])->findOrFail($id);
        return response()->json($os);
    }

    public function getWatchDashboard(Request $request)
    {
        $user = $request->user();
        $orders = OrdemServico::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'running'])
            ->with(['client', 'veiculo'])
            ->latest()
            ->get()
            ->map(function ($os) {
                return [
                    'id' => $os->id,
                    'client' => $os->client ? ($os->client->name ?: $os->client->company_name) : 'N/A',
                    'vehicle' => $os->veiculo ? $os->veiculo->modelo : 'N/A',
                    'status' => $os->status,
                ];
            });

        return response()->json([
            'user_name' => $user->name,
            'orders' => $orders
        ]);
    }

    public function getDashboardStats(Request $request)
    {
        $user = $request->user();
        $formatOS = function ($os) {
            return [
                'id' => $os->id,
                'client_name' => $os->client ? ($os->client->name ?: $os->client->company_name) : 'Cliente Removido',
                'vehicle' => $os->veiculo ? $os->veiculo->modelo : 'Veículo Removido',
                'plate' => $os->veiculo ? $os->veiculo->placa : 'N/A',
                'status' => $os->status,
                'total' => $os->total,
                'created_at' => $os->created_at
            ];
        };

        if ($user->role === 'admin') {
            $today = Carbon::today();
            $month = Carbon::now()->month;
            $year = Carbon::now()->year;

            // Current Month Revenue
            $financialRevenue = FinancialTransaction::where('type', 'in')->where('status', 'paid')
                ->whereMonth('paid_at', $month)->whereYear('paid_at', $year)->sum('amount');
            $osRevenue = OrdemServico::whereIn('status', ['paid', 'finalized', 'completed'])
                ->whereMonth('updated_at', $month)->whereYear('updated_at', $year)
                ->get()->sum('total');
            $monthlyRevenue = $financialRevenue + $osRevenue;

            // Last Month Revenue for Growth
            $lastMonth = Carbon::now()->subMonth();
            $financialRevenueLast = FinancialTransaction::where('type', 'in')->where('status', 'paid')
                ->whereMonth('paid_at', $lastMonth->month)->whereYear('paid_at', $lastMonth->year)->sum('amount');
            $osRevenueLast = OrdemServico::whereIn('status', ['paid', 'finalized', 'completed'])
                ->whereMonth('updated_at', $lastMonth->month)->whereYear('updated_at', $lastMonth->year)
                ->get()->sum('total');
            $revenueLastMonth = $financialRevenueLast + $osRevenueLast;
            $revenueGrowth = round($revenueLastMonth > 0 ? (($monthlyRevenue - $revenueLastMonth) / $revenueLastMonth) * 100 : ($monthlyRevenue > 0 ? 100 : 0), 1);

            // Expenses for Profitability
            $monthlyExpenses = FinancialTransaction::where('type', 'out')->where('status', 'paid')
                ->whereMonth('paid_at', $month)->whereYear('paid_at', $year)->sum('amount');
            $monthlyProfitability = round($monthlyRevenue > 0 ? (($monthlyRevenue - $monthlyExpenses) / $monthlyRevenue) * 100 : 0, 1);


            $osStats = [
                'pending' => OrdemServico::where('status', 'pending')->count(),
                'running' => OrdemServico::where('status', 'running')->count(),
                'finalized_today' => OrdemServico::where('status', 'finalized')->whereDate('updated_at', $today)->count(),
            ];
            return response()->json([
                'monthlyRevenue' => $monthlyRevenue,
                'revenueGrowth' => $revenueGrowth,
                'monthlyProfitability' => $monthlyProfitability,
                'totalClients' => Clients::count(),
                'osStats' => $osStats,
                'lowStockCount' => 3,
                'pendingBudgetsCount' => Budget::where('status', 'pending')->count(),
                'recentOS' => OrdemServico::whereIn('status', ['pending', 'running'])->with(['client', 'veiculo'])->latest()->take(10)->get()->map($formatOS),
                'unreadNotificationsCount' => $user->unreadNotifications->count()
            ]);
        }

        $stats = [
            'runningOS' => OrdemServico::where('user_id', $user->id)->where('status', 'running')->count(),
            'completedToday' => OrdemServico::where('user_id', $user->id)->where('status', 'finalized')->whereDate('updated_at', Carbon::today())->count(),
            'pendingBudgets' => Budget::where('user_id', $user->id)->where('status', 'pending')->count(),
        ];

        return response()->json([
            'stats' => $stats,
            'recentOS' => OrdemServico::where('user_id', $user->id)->whereIn('status', ['pending', 'running'])->with(['client', 'veiculo'])->latest()->take(10)->get()->map($formatOS),
            'unreadNotificationsCount' => $user->unreadNotifications->count()
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,running,finalized,canceled']);
        $os = OrdemServico::findOrFail($id);
        $os->status = $request->status;
        $os->save();
        return response()->json(['message' => 'Status atualizado', 'os' => $os]);
    }

    public function toggleTimer($itemId)
    {
        $item = \App\Models\OrdemServicoItem::findOrFail($itemId);
        if ($item->status === 'in_progress') {
            $item->stopTimer();
        } else {
            $item->startTimer();
        }
        return response()->json(['success' => true, 'item' => $item]);
    }

    public function completeItem($itemId)
    {
        $item = \App\Models\OrdemServicoItem::findOrFail($itemId);
        $item->complete();
        return response()->json(['success' => true, 'item' => $item]);
    }
}
