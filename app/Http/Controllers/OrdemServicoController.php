<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrdemServicoRequest;
use App\Http\Requests\UpdateOrdemServicoRequest;
use App\Http\Resources\OrdemServicoResource;
use App\Jobs\SendPushNotificationJob;
use App\Services\OrdemServicoService;
use Illuminate\Http\Request;
use App\Models\OrdemServico;
use App\Models\Clients;
use App\Models\Vehicles;
use App\Models\Service;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\Auth;

class OrdemServicoController extends Controller
{
    protected $service;

    public function __construct(OrdemServicoService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('content.pages.ordens-servico.index');
    }

    public function dataBase(Request $request)
    {
        $query = OrdemServico::with(['client', 'veiculo', 'user']);

        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        // Search logic (opcional, mas recomendado para performance)
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            });
        }

        $totalData = $query->count();

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        if ($length > 0) {
            $query->offset($start)->limit($length);
        }

        $items = $query->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalData,
            'data' => OrdemServicoResource::collection($items)
        ]);
    }

    public function create()
    {
        $clients = Clients::all();
        $services = Service::where('is_active', true)->get();
        $parts = InventoryItem::where('is_active', true)->get();

        // Carrega campos disponíveis para nova OS
        $customFields = (new OrdemServico())->getAvailableCustomFields();

        return view('content.pages.ordens-servico.create', compact('clients', 'services', 'parts', 'customFields'));
    }

    public function store(StoreOrdemServicoRequest $request)
    {
        try {
            $os = $this->service->store($request->validated());

            if ($request->has('redirect_to_checklist')) {
                return redirect()->route('ordens-servico.checklist.create', ['os_id' => $os->id])
                    ->with('success', 'OS Criada! Realize o checklist agora.')
                    ->with('just_created_os', $os->id);
            }

            return redirect()->route('ordens-servico')->with('success', 'OS Criada!')->with('just_created_os', $os->id);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $os = OrdemServico::findOrFail($id);
        $oldStatus = $os->status;
        $os->update(['status' => $request->status]);

        $statusLabels = [
            'pending' => 'Pendente',
            'in_progress' => 'Em Manutenção',
            'testing' => 'Em Teste',
            'cleaning' => 'Em Limpeza',
            'completed' => 'Pronto para Retirada',
            'paid' => 'Finalizado / Pago',
            'awaiting_approval' => 'Aguardando Aprovação'
        ];

        if ($oldStatus !== $request->status && in_array($request->status, ['in_progress', 'testing', 'completed', 'paid'])) {
            $eventType = in_array($request->status, ['paid', 'completed']) ? 'os_finalizada' : 'os_em_andamento';

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

        if (Auth::user()->expo_push_token) {
            $msg = match ($request->status) {
                'in_progress' => "O mecânico começou a trabalhar na OS #{$os->id}.",
                'completed' => "A OS #{$os->id} foi finalizada e está pronta para entrega!",
                default => null
            };

            if ($msg) {
                SendPushNotificationJob::dispatch(Auth::user()->expo_push_token, "Status da OS Atualizado 🛠️", $msg);
            }
        }

        return response()->json(['success' => true]);
    }

    public function getVehiclesByClient($clientId)
    {
        $vehicles = Vehicles::withoutGlobalScope('company')->where('cliente_id', $clientId)->get();
        return response()->json($vehicles);
    }

    public function edit($id)
    {
        $order = OrdemServico::with(['items', 'parts'])->findOrFail($id);
        $clients = Clients::all();
        $services = Service::where('is_active', true)->get();
        $parts = InventoryItem::where('is_active', true)->get();
        $vehicles = Vehicles::where('cliente_id', $order->client_id)->get();

        // Carrega campos personalizados preenchidos
        $customFields = $order->getCustomFieldsWithValues();

        return view('content.pages.ordens-servico.edit', compact('order', 'clients', 'services', 'parts', 'vehicles', 'customFields'));
    }

    public function update(UpdateOrdemServicoRequest $request, $id)
    {
        try {
            $os = OrdemServico::findOrFail($id);
            $this->service->update($os, $request->validated());

            return redirect()->route('ordens-servico')->with('success', 'OS Atualizada com Sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function printLabel($id)
    {
        $os = OrdemServico::with('client')->findOrFail($id);
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $os->id;

        return view('content.pages.ordens-servico.print-label', compact('os', 'qrCodeUrl'));
    }
}
