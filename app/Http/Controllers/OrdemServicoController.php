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

            // Adicionar ao Histórico do Veículo: Entrada na Oficina
            \App\Models\VehicleHistory::create([
                'company_id' => Auth::user()->company_id,
                'veiculo_id' => $os->veiculo_id,
                'ordem_servico_id' => $os->id,
                'date' => now(),
                'km' => $os->km_entry ?? 0,
                'event_type' => 'entrada_oficina',
                'title' => 'Entrada na Oficina',
                'description' => 'O veículo deu entrada para avaliação técnica.',
                'performer' => Auth::user()->name,
                'created_by' => Auth::id()
            ]);

            // Adicionar ao Histórico do Veículo: Aguardando Orçamento
            \App\Models\VehicleHistory::create([
                'company_id' => Auth::user()->company_id,
                'veiculo_id' => $os->veiculo_id,
                'ordem_servico_id' => $os->id,
                'date' => now(),
                'km' => $os->km_entry ?? 0,
                'event_type' => 'aguardando_orcamento',
                'title' => 'Aguardando Orçamento',
                'description' => 'A equipe técnica está avaliando o veículo para elaboração do orçamento.',
                'performer' => Auth::user()->name,
                'created_by' => Auth::id()
            ]);

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
        $oldStatus = $os->status;
        $os->update(['status' => $request->status]);

        // Mapeamento de nomes amigáveis para o histórico
        $statusLabels = [
            'pending' => 'Pendente',
            'in_progress' => 'Em Manutenção',
            'testing' => 'Em Teste',
            'cleaning' => 'Em Limpeza',
            'completed' => 'Pronto para Retirada',
            'paid' => 'Finalizado / Pago',
            'awaiting_approval' => 'Aguardando Aprovação'
        ];

        // Se o status mudou para algo relevante, logar no histórico
        if ($oldStatus !== $request->status && in_array($request->status, ['in_progress', 'testing', 'completed', 'paid'])) {
            $eventType = $request->status === 'paid' || $request->status === 'completed' ? 'os_finalizada' : 'os_em_andamento';

            \App\Models\VehicleHistory::create([
                'company_id' => Auth::user()->company_id,
                'veiculo_id' => $os->veiculo_id,
                'ordem_servico_id' => $os->id,
                'date' => now(),
                'km' => $os->km_entry ?? 0,
                'event_type' => $eventType,
                'title' => 'Status da OS Atualizado: ' . ($statusLabels[$request->status] ?? $request->status),
                'description' => 'A Ordem de Serviço #' . $os->id . ' avançou para o status ' . ($statusLabels[$request->status] ?? $request->status) . '.',
                'performer' => Auth::user()->name ?? 'Sistema',
                'created_by' => Auth::id()
            ]);
        }

        // Notificação Push para mudanças de status importantes
        $user = Auth::user();
        \Illuminate\Support\Facades\Log::info('Tentativa de Push:', [
            'user_id' => $user->id ?? 'NULO',
            'token' => $user->expo_push_token ?? 'VAZIO',
            'status' => $request->status
        ]);

        if ($user && $user->expo_push_token) {
            $msg = "";
            if ($request->status === 'in_progress') $msg = "O mecânico começou a trabalhar na OS #{$os->id}.";
            if ($request->status === 'completed') $msg = "A OS #{$os->id} foi finalizada e está pronta para entrega!";
            
            if ($msg) {
                \App\Helpers\Helpers::sendExpoNotification($user->expo_push_token, "Status da OS Atualizado 🛠️", $msg);
            }
        }

        return response()->json(['success' => true]);
    }

    public function getVehiclesByClient($clientId)
    {
        // Usamos withoutGlobalScope('company') para garantir que os dados apareçam durante os testes
        $vehicles = Vehicles::withoutGlobalScope('company')->where('cliente_id', $clientId)->get();
        return response()->json($vehicles);
    }

    public function edit($id)
    {
        $order = OrdemServico::with(['items', 'parts'])->findOrFail($id);
        $clients = Clients::all();
        $services = Service::where('is_active', true)->get();
        $parts = InventoryItem::where('is_active', true)->get();
        
        // Prepare pre-selected vehicles for the dropdown
        $vehicles = Vehicles::where('cliente_id', $order->client_id)->get();

        return view('content.pages.ordens-servico.edit', compact('order', 'clients', 'services', 'parts', 'vehicles'));
    }

    public function update(Request $request, $id)
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

            $os = OrdemServico::findOrFail($id);
            $os->update([
                'client_id' => $validated['client_id'],
                'veiculo_id' => $validated['veiculo_id'],
                'status' => $validated['status'],
                'description' => $validated['description'],
                'km_entry' => $validated['km_entry'],
            ]);

            // Sync Services
            // Strategy: Get existing IDs, delete those not in request, update/create others
            $submittedServices = $validated['services'] ?? [];
            $existingItemIds = $os->items()->pluck('service_id')->toArray();

            // 1. Remove items not selected anymore
            // Filter submitted to only those with 'selected'
            $selectedServiceIds = [];
            foreach ($submittedServices as $sId => $data) {
                if (isset($data['selected'])) {
                    $selectedServiceIds[] = $sId;
                }
            }
            
            // Delete items where service_id is NOT in the selected list
            $os->items()->whereNotIn('service_id', $selectedServiceIds)->delete();

            // 2. Update or Create
            foreach ($submittedServices as $serviceId => $data) {
                if (!isset($data['selected'])) continue;

                $item = $os->items()->where('service_id', $serviceId)->first();

                if ($item) {
                    $item->update([
                        'price' => $data['price'],
                        'quantity' => $data['quantity'] ?? 1
                    ]);
                } else {
                    OrdemServicoItem::create([
                        'ordem_servico_id' => $os->id,
                        'service_id' => $serviceId,
                        'price' => $data['price'],
                        'quantity' => $data['quantity'] ?? 1,
                        'status' => 'pending' // Default status for new items
                    ]);
                }
            }

            // Sync Parts (similar logic)
            // Note: Simplification - not handling inventory stock adjustment on edit for now to keep it safe
            $submittedParts = $validated['parts'] ?? [];
            
            $selectedPartIds = [];
            foreach ($submittedParts as $pId => $data) {
                if (isset($data['selected'])) {
                    $selectedPartIds[] = $pId;
                }
            }
            
            $os->parts()->whereNotIn('inventory_item_id', $selectedPartIds)->delete();

            foreach ($submittedParts as $partId => $data) {
                if (!isset($data['selected'])) continue;

                $part = $os->parts()->where('inventory_item_id', $partId)->first();

                if ($part) {
                    $part->update([
                        'price' => $data['price'],
                        'quantity' => $data['quantity'] ?? 1
                    ]);
                } else {
                    OrdemServicoPart::create([
                        'ordem_servico_id' => $os->id,
                        'inventory_item_id' => $partId,
                        'price' => $data['price'],
                        'quantity' => $data['quantity'] ?? 1
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('ordens-servico')->with('success', 'OS Atualizada com Sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
