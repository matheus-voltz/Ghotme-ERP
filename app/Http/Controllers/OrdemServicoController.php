<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdemServico;
use App\Models\OrdemServicoItem;
use App\Models\OrdemServicoPart;
use App\Models\Clients;
use App\Models\Vehicles;
use App\Models\Service;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrdemServicoController extends Controller
{
    public function index()
    {
        return view('content.pages.ordens-servico.index');
    }

    public function dataBase(Request $request)
    {
        $query = OrdemServico::with(['client', 'veiculo']);
        
        $totalData = $query->count();
        $totalFiltered = $totalData;

        $items = $query->orderBy('created_at', 'desc')->get();

        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'id' => $item->id,
                'client' => $item->client ? ($item->client->name ?? $item->client->company_name) : '-',
                'vehicle' => $item->veiculo ? "{$item->veiculo->placa} - {$item->veiculo->modelo}" : '-',
                'status' => $item->status,
                'total' => $item->total,
                'date' => $item->created_at->format('d/m/Y')
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $clients = Clients::all();
        $services = Service::where('is_active', true)->get();
        $parts = InventoryItem::where('is_active', true)->get();
        return view('content.pages.ordens-servico.create', compact('clients', 'services', 'parts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'veiculo_id' => 'required|exists:veiculos,id',
            'status' => 'required',
            'description' => 'nullable|string',
            'km_entry' => 'nullable|integer',
            'services' => 'nullable|array',
            'parts' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $os = OrdemServico::create([
                'client_id' => $validated['client_id'],
                'veiculo_id' => $validated['veiculo_id'],
                'status' => $validated['status'],
                'description' => $validated['description'],
                'km_entry' => $validated['km_entry'],
                'user_id' => Auth::id()
            ]);

            if (!empty($validated['services'])) {
                foreach ($validated['services'] as $id => $data) {
                    if (!isset($data['selected'])) continue;
                    OrdemServicoItem::create([
                        'ordem_servico_id' => $os->id,
                        'service_id' => $id,
                        'price' => $data['price'],
                        'quantity' => $data['quantity'] ?? 1
                    ]);
                }
            }

            if (!empty($validated['parts'])) {
                foreach ($validated['parts'] as $id => $data) {
                    if (!isset($data['selected'])) continue;
                    OrdemServicoPart::create([
                        'ordem_servico_id' => $os->id,
                        'inventory_item_id' => $id,
                        'price' => $data['price'],
                        'quantity' => $data['quantity'] ?? 1
                    ]);
                }
            }

            DB::commit();
            
            if ($request->has('redirect_to_checklist')) {
                return redirect()->route('ordens-servico.checklist.create', ['os_id' => $os->id])->with('success', 'OS Criada! Realize o checklist agora.');
            }

            return redirect()->route('ordens-servico')->with('success', 'OS Criada!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $os = OrdemServico::findOrFail($id);
        $os->update(['status' => $request->status]);
        return response()->json(['success' => true]);
    }

    public function getVehiclesByClient($clientId)
    {
        $vehicles = Vehicles::where('cliente_id', $clientId)->get();
        return response()->json($vehicles);
    }
}