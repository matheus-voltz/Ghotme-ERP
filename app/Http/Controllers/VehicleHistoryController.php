<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicles;
use App\Models\VehicleHistory;
use Illuminate\Support\Facades\Auth;

class VehicleHistoryController extends Controller
{
    /**
     * Exibe a página principal de histórico.
     */
    public function index()
    {
        return view('content.pages.vehicles.history-index');
    }

    /**
     * API para buscar veículo por placa ou chassi.
     */
    public function search(Request $request)
    {
        $term = $request->input('q');

        $vehicles = Vehicles::where('placa', 'LIKE', "%{$term}%")
            ->orWhere('renavam', 'LIKE', "%{$term}%")
            ->orWhere('chassi', 'LIKE', "%{$term}%")
            ->with('client') // Relacionamento correto definido no Model Vehicles
            ->limit(10)
            ->get();

        return response()->json($vehicles->map(function ($v) {
            return [
                'id' => $v->id,
                'text' => "{$v->placa} - {$v->modelo} ({$v->marca})",
                'client_name' => $v->client ? ($v->client->name ?? $v->client->company_name) : 'Sem dono',
                'full_data' => $v
            ];
        }));
    }

    /**
     * Retorna o histórico completo de um veículo (Timeline).
     */
    public function getTimeline($vehicleId)
    {
        $histories = VehicleHistory::where('veiculo_id', $vehicleId)
            ->with(['creator', 'ordemServico'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($histories);
    }

    /**
     * Salva um novo evento no histórico (Manual).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'veiculo_id' => 'required|exists:veiculos,id',
            'date' => 'required|date',
            'km' => 'required|integer|min:0',
            'title' => 'required|string|max:255',
            'event_type' => 'required|string',
            'description' => 'nullable|string',
            'performer' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $validated['created_by'] = Auth::id();

        // Se for manutenção externa, o performer é obrigatório ou default
        if (empty($validated['performer'])) {
            $validated['performer'] = 'Externo / Desconhecido';
        }

        $history = VehicleHistory::create($validated);

        // Atualiza KM do veículo se o novo for maior
        $vehicle = Vehicles::find($validated['veiculo_id']);
        if ($vehicle && $validated['km'] > $vehicle->km_atual) {
            $vehicle->km_atual = $validated['km'];
            $vehicle->save();
        }

        return response()->json(['success' => true, 'message' => 'Evento adicionado ao histórico!', 'data' => $history]);
    }
}
