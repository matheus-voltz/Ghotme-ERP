<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use Illuminate\Http\Request;

class ApiOrdemServicoController extends Controller
{
    public function index(Request $request)
    {
        // Simple pagination for API
        $query = OrdemServico::with(['client', 'vehicle', 'mechanic'])
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

    public function show($id)
    {
        $os = OrdemServico::with(['client', 'vehicle', 'mechanic', 'services', 'products'])
            ->findOrFail($id);

        return response()->json($os);
    }
}
