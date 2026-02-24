<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\OrdemServicoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        $employee = Auth::user();

        // 1. Ordens Ativas (que estão no pátio/oficina)
        $orders = OrdemServico::where('company_id', $employee->company_id)
            ->whereIn('status', ['approved', 'in_progress', 'testing', 'cleaning'])
            ->with(['client', 'veiculo', 'items.service'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Ordens Finalizadas (Total Acumulado)
        $finalizedCount = OrdemServico::where('company_id', $employee->company_id)
            ->whereIn('status', ['completed', 'paid'])
            ->count();

        // 3. Ordens Pausadas (tem pelo menos um item pausado nas OS ativas)
        $pausedCount = OrdemServico::where('company_id', $employee->company_id)
            ->whereHas('items', function($q) {
                $q->where('status', 'paused');
            })
            ->whereIn('status', ['approved', 'in_progress', 'testing', 'cleaning'])
            ->count();

        // 4. Tempo de Produção Total (Soma de tudo + tempo rodando agora)
        $totalSeconds = OrdemServicoItem::whereHas('ordemServico', function($q) use ($employee) {
                $q->where('company_id', $employee->company_id);
            })
            ->sum('duration_seconds');

        // Adiciona o tempo dos itens que estão com o cronômetro ligado agora
        $activeItems = OrdemServicoItem::whereHas('ordemServico', function($q) use ($employee) {
                $q->where('company_id', $employee->company_id);
            })
            ->where('status', 'in_progress')
            ->whereNotNull('started_at')
            ->get();

        foreach($activeItems as $ai) {
            $totalSeconds += $ai->started_at->diffInSeconds(now());
        }

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $productionTime = "{$hours}h {$minutes}m";

        return view('content.employee.dashboard', compact('orders', 'finalizedCount', 'pausedCount', 'productionTime'));
    }

    public function show($uuid)
    {
        $order = OrdemServico::where('uuid', $uuid)
            ->with(['client', 'veiculo']) 
            ->firstOrFail();

        $items = OrdemServicoItem::where('ordem_servico_id', $order->id)->with('service')->get();
        $order->setRelation('items', $items);

        return view('content.employee.show', compact('order'));
    }

    public function toggleTimer($itemId)
    {
        $item = OrdemServicoItem::findOrFail($itemId);

        // Check if item belongs to company of auth user for security
        if ($item->ordemServico->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($item->status === 'in_progress') {
            $item->stopTimer();
            $message = 'Timer paused';
        } else {
            // Stop other timers in the same order if needed? For now, allow parallel.
            $item->startTimer();
            $message = 'Timer started';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'status' => $item->status,
            'elapsed_time' => $item->elapsed_time
        ]);
    }

    public function completeItem($itemId)
    {
        $item = OrdemServicoItem::findOrFail($itemId);

        if ($item->ordemServico->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $item->complete();

        return response()->json([
            'success' => true,
            'message' => 'Item completed',
            'status' => $item->status,
            'elapsed_time' => $item->elapsed_time
        ]);
    }
}
