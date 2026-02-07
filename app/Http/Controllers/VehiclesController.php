<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicles;

class VehiclesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('content.pages.vehicles.vehicles-index');
    }

    public function dataBase(Request $request)
    {
        $columns = [
            0 => 'id',
            1 => 'id',
            2 => 'placa',
            3 => 'marca',
            4 => 'modelo',
            5 => 'ano_fabricacao',
            6 => 'ativo',
            7 => 'id',
        ];

        $totalData = Vehicles::count();
        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')] ?? 'id';
        $dir = $request->input('order.0.dir') ?? 'desc';

        if (empty($request->input('search.value'))) {
            $vehicles = Vehicles::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');

            $vehicles = Vehicles::where('id', 'LIKE', "%{$search}%")
                ->orWhere('placa', 'LIKE', "%{$search}%")
                ->orWhere('renavam', 'LIKE', "%{$search}%")
                ->orWhere('marca', 'LIKE', "%{$search}%")
                ->orWhere('modelo', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = Vehicles::where('id', 'LIKE', "%{$search}%")
                ->orWhere('placa', 'LIKE', "%{$search}%")
                ->orWhere('renavam', 'LIKE', "%{$search}%")
                ->orWhere('marca', 'LIKE', "%{$search}%")
                ->orWhere('modelo', 'LIKE', "%{$search}%")
                ->count();
        }

        $data = [];
        $ids = $start;

        foreach ($vehicles as $vehicle) {
            $nestedData['fake_id'] = ++$ids;
            $nestedData['id'] = $vehicle->id;
            $nestedData['placa'] = $vehicle->placa;
            $nestedData['renavam'] = $vehicle->renavam; // JS expects 'renavam'
            $nestedData['ativo'] = $vehicle->ativo;
            $nestedData['marca'] = $vehicle->marca;
            $nestedData['modelo'] = $vehicle->modelo;
            $nestedData['ano_fabricacao'] = $vehicle->ano_fabricacao;
            $nestedData['action'] = '';

            $data[] = $nestedData;
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => intval($totalData),
            'recordsFiltered' => intval($totalFiltered),
            'data' => $data
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clients,id',
            'placa' => 'required|string|max:10',
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:80',
            'ano_fabricacao' => 'nullable|numeric',
            'renavam' => 'nullable|string|max:20',
            'ativo' => 'required|boolean',
        ], [], [
            'cliente_id' => 'Cliente',
            'placa' => 'Placa',
            'marca' => 'Marca',
            'modelo' => 'Modelo'
        ]);

        $vehicleId = $request->id;

        $data = [
            'cliente_id' => $request->cliente_id,
            'placa' => strtoupper($request->placa),
            'renavam' => $request->renavam,
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'ano_fabricacao' => $request->ano_fabricacao,
            'ativo' => $request->ativo,
            'company_id' => auth()->user()->company_id // Garante o Multi-tenancy
        ];

        if ($vehicleId) {
            $vehicle = Vehicles::findOrFail($vehicleId);
            $vehicle->update($data);
            $status = 'atualizado';
        } else {
            Vehicles::create($data);
            $status = 'criado';
        }

        return response()->json(['success' => true, 'message' => "Veículo {$status} com sucesso!"]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $vehicle = Vehicles::findOrFail($id);
        return response()->json($vehicle);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vehicle = Vehicles::findOrFail($id);
        $vehicle->delete();
        return response()->json(['message' => 'Vehicle deleted successfully']);
    }

    public function getDossier($id)
    {
        $vehicle = Vehicles::with(['client', 'company'])->findOrFail($id);

        // Buscar Histórico de OS (Concluídas e Em andamento)
        $history = \App\Models\OrdemServico::where('veiculo_id', $id)
            ->with(['items.service', 'parts.inventoryItem']) // Carregar itens e peças
            ->orderBy('created_at', 'desc')
            ->get();

        return view('content.pages.vehicles.partials.dossier', compact('vehicle', 'history'));
    }
}
