<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OsTechnicalChecklist;
use App\Models\OrdemServico;

class ApiTechnicalChecklistController extends Controller
{
    public function index($osId)
    {
        $checklist = OsTechnicalChecklist::where('ordem_servico_id', $osId)->get();
        return response()->json($checklist);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ordem_servico_id' => 'required|exists:ordem_servicos,id',
            'items' => 'required|array',
            'items.*.category' => 'required|string',
            'items.*.item' => 'required|string',
            'items.*.status' => 'required|in:ok,warning,danger',
        ]);

        // Remove checklist anterior para sobrescrever (ou pode ser incremental, depende da regra)
        OsTechnicalChecklist::where('ordem_servico_id', $request->ordem_servico_id)->delete();

        foreach ($request->items as $item) {
            OsTechnicalChecklist::create([
                'ordem_servico_id' => $request->ordem_servico_id,
                'category' => $item['category'],
                'item' => $item['item'],
                'status' => $item['status'],
                'observation' => $item['observation'] ?? null,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Checklist técnico salvo com sucesso!']);
    }
}