<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdemServicoItem;
use Illuminate\Http\Request;

class ApiOrdemServicoItemController extends Controller
{
    public function toggleTimer(Request $request, $id)
    {
        $item = OrdemServicoItem::findOrFail($id);

        if ($item->status === 'in_progress') {
            $item->stopTimer();
            $message = 'Timer pausado';
        } else {
            $item->startTimer();
            $message = 'Timer iniciado';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'item' => $item->fresh(['service'])
        ]);
    }

    public function completeItem(Request $request, $id)
    {
        $item = OrdemServicoItem::findOrFail($id);
        $item->complete();

        return response()->json([
            'success' => true,
            'message' => 'Serviço concluído',
            'item' => $item->fresh(['service'])
        ]);
    }
}
