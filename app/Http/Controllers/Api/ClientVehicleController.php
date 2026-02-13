<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Clients;
use App\Models\Vehicles;
use App\Models\OrdemServico;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientVehicleController extends Controller
{
    public function getClients()
    {
        $companyId = Auth::user()->company_id ?? Auth::id(); // Fallback to user ID if company_id is null

        $clients = Clients::where('company_id', $companyId)
            ->select('id', 'name', 'company_name')
            ->orderBy('name')
            ->get();
        return response()->json($clients);
    }

    public function getVehicles($clientId)
    {
        // Corrigido de client_id para cliente_id conforme a migração
        $vehicles = Vehicles::where('cliente_id', $clientId)
            ->select('id', 'placa', 'modelo', 'marca')
            ->get();
        return response()->json($vehicles);
    }

    /* For future implementation if we have separate tables for Services and Parts */
    // public function getServicesAndParts() { ... }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'veiculo_id' => 'required|exists:veiculos,id',
            'status' => 'required|string',
            'km_entry' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $os = OrdemServico::create([
                'company_id' => Auth::user()->company_id,
                'client_id'  => $request->client_id,
                'veiculo_id' => $request->veiculo_id,
                'user_id'    => Auth::id(),
                'status'     => $request->status,
                'km_entry'   => $request->km_entry ?? 0,
                'description' => $request->description,
                'opened_at'  => now(),
            ]);

            // If we had services/parts logic here we would attach them
            // For now, MVP just creates the OS header

            DB::commit();

            return response()->json(['success' => true, 'message' => 'OS criada com sucesso!', 'id' => $os->id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro ao criar OS: ' . $e->getMessage()], 500);
        }
    }
}
