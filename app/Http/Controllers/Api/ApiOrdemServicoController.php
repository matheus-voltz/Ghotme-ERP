<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use App\Models\Budget;
use App\Models\InventoryItem;
use App\Models\Clients;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ApiOrdemServicoController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdemServico::with(['client', 'veiculo', 'user'])->latest();
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        return response()->json($query->paginate(20));
    }

    public function show($id)
    {
        $os = OrdemServico::with(['client', 'veiculo', 'user', 'items', 'parts'])
            ->findOrFail($id);
        return response()->json($os);
    }

    public function getDashboardStats(Request $request)
    {
        $user = $request->user();
        
        // Stats para Administrador
        if ($user->role === 'admin') {
            $monthlyRevenue = OrdemServico::where('status', 'finalized')
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('total');

            $osStats = [
                'pending' => OrdemServico::where('status', 'pending')->count(),
                'running' => OrdemServico::where('status', 'running')->count(),
                'finalized_today' => OrdemServico::where('status', 'finalized')
                    ->whereDate('updated_at', Carbon::today())->count(),
            ];

            return response()->json([
                'monthlyRevenue' => $monthlyRevenue,
                'revenueGrowth' => 15.5, // Mock ou calcular vs mês anterior
                'monthlyProfitability' => 65,
                'totalClients' => Clients::count(),
                'osStats' => $osStats,
                'lowStockCount' => 3,
                'pendingBudgetsCount' => Budget::where('status', 'pending')->count(),
                'recentOS' => OrdemServico::with(['client', 'veiculo'])->latest()->take(5)->get()->map(function($os) {
                    return [
                        'id' => $os->id,
                        'client_name' => $os->client ? ($os->client->name ?: $os->client->company_name) : 'Cliente Removido',
                        'vehicle' => $os->veiculo ? $os->veiculo->modelo : 'Veículo Removido',
                        'plate' => $os->veiculo ? $os->veiculo->placa : 'N/A',
                        'status' => $os->status,
                        'total' => $os->total,
                        'created_at' => $os->created_at
                    ];
                })
            ]);
        }

        // Stats para Mecânico
        $stats = [
            'runningOS' => OrdemServico::where('user_id', $user->id)->where('status', 'running')->count(),
            'completedToday' => OrdemServico::where('user_id', $user->id)
                ->where('status', 'finalized')
                ->whereDate('updated_at', Carbon::today())->count(),
            'pendingBudgets' => Budget::where('user_id', $user->id)->where('status', 'pending')->count(),
        ];

        return response()->json([
            'stats' => $stats,
            'recentOS' => OrdemServico::where('user_id', $user->id)
                ->with(['client', 'veiculo'])
                ->latest()->take(5)->get()->map(function($os) {
                    return [
                        'id' => $os->id,
                        'client_name' => $os->client ? ($os->client->name ?: $os->client->company_name) : 'Cliente Removido',
                        'vehicle' => $os->veiculo ? $os->veiculo->modelo : 'Veículo Removido',
                        'plate' => $os->veiculo ? $os->veiculo->placa : 'N/A',
                        'status' => $os->status,
                        'total' => $os->total,
                        'created_at' => $os->created_at
                    ];
                })
        ]);
    }

        public function updateStatus(Request $request, $id)

        {

            $request->validate([

                'status' => 'required|in:pending,running,finalized,canceled'

            ]);

    

            $os = OrdemServico::findOrFail($id);

            $os->status = $request->status;

            $os->save();

    

            return response()->json([

                'message' => 'Status atualizado com sucesso',

                'os' => $os

            ]);

        }

    

        public function toggleTimer($itemId)

        {

            $item = \App\Models\OrdemServicoItem::findOrFail($itemId);

            

            if ($item->status === 'in_progress') {

                // Pausar

                $item->status = 'paused';

                $item->duration_seconds += now()->diffInSeconds($item->started_at);

                $item->started_at = null;

            } else {

                // Iniciar/Retomar

                $item->status = 'in_progress';

                $item->started_at = now();

            }

            

            $item->save();

            return response()->json(['success' => true, 'item' => $item]);

        }

    

        public function completeItem($itemId)

        {

            $item = \App\Models\OrdemServicoItem::findOrFail($itemId);

            

            // Se estava rodando, soma o tempo final

            if ($item->status === 'in_progress' && $item->started_at) {

                $item->duration_seconds += now()->diffInSeconds($item->started_at);

            }

            

            $item->status = 'completed';

            $item->started_at = null;

            $item->save();

            

            return response()->json(['success' => true, 'item' => $item]);

        }

    }

    