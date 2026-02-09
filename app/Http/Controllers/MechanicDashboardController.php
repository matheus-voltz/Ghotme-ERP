<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\OrdemServicoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MechanicDashboardController extends Controller
{
    public function index()
    {
        $mechanic = Auth::user();

        // Fetch orders assigned to the mechanic or all active orders if no assignment logic is strict yet
        // Assuming 'user_id' on OrdemServico means the responsible mechanic/advisor
        $orders = OrdemServico::where('company_id', $mechanic->company_id)
            ->whereIn('status', ['approved', 'in_progress', 'testing', 'cleaning'])
            ->with(['client', 'veiculo', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('content.mechanic.dashboard', compact('orders'));
    }

    public function show($uuid)
    {
        $order = OrdemServico::where('uuid', $uuid)
            ->with(['client', 'veiculo', 'items.service'])
            ->firstOrFail();

        return view('content.mechanic.show', compact('order'));
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
