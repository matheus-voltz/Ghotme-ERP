<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\BudgetPart;
use App\Models\Clients;
use App\Models\Vehicles;
use App\Models\Service;
use App\Models\InventoryItem;
use App\Models\OrdemServico;
use App\Models\OrdemServicoItem;
use App\Models\OrdemServicoPart;
use App\Models\AppSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->segment(2); // pending, approved, rejected
        return view('content.pages.budgets.index', compact('status'));
    }

    public function dataBase(Request $request)
    {
        $status = $request->input('status');
        $query = Budget::with(['client', 'veiculo']);
        
        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'id' => $item->id,
                'client' => $item->client ? ($item->client->name ?? $item->client->company_name) : '-',
                'vehicle' => $item->veiculo ? "{$item->veiculo->placa} - {$item->veiculo->modelo}" : '-',
                'status' => $item->status,
                'total' => $item->total,
                'date' => $item->created_at->format('d/m/Y'),
                'valid_until' => $item->valid_until ? $item->valid_until->format('d/m/Y') : '-'
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $clients = Clients::all();
        $services = Service::where('is_active', true)->get();
        $parts = InventoryItem::where('is_active', true)->get();
        $appSettings = AppSetting::first();
        $validityDays = $appSettings->budget_validity_days ?? 7;
        
        return view('content.pages.budgets.create', compact('clients', 'services', 'parts', 'validityDays'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'veiculo_id' => 'required|exists:vehicles,id',
            'valid_until' => 'required|date',
            'description' => 'nullable|string',
            'services' => 'nullable|array',
            'parts' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $budget = Budget::create([
                'client_id' => $validated['client_id'],
                'veiculo_id' => $validated['veiculo_id'],
                'status' => 'pending',
                'valid_until' => $validated['valid_until'],
                'description' => $validated['description'],
                'user_id' => Auth::id()
            ]);

            if (!empty($validated['services'])) {
                foreach ($validated['services'] as $id => $data) {
                    if (!isset($data['selected'])) continue;
                    BudgetItem::create([
                        'budget_id' => $budget->id,
                        'service_id' => $id,
                        'price' => $data['price'],
                        'quantity' => $data['quantity'] ?? 1
                    ]);
                }
            }

            if (!empty($validated['parts'])) {
                foreach ($validated['parts'] as $id => $data) {
                    if (!isset($data['selected'])) continue;
                    BudgetPart::create([
                        'budget_id' => $budget->id,
                        'inventory_item_id' => $id,
                        'price' => $data['price'],
                        'quantity' => $data['quantity'] ?? 1
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('budgets.pending')->with('success', 'Orçamento Criado!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $budget = Budget::findOrFail($id);
        $budget->update(['status' => $request->status]);
        return response()->json(['success' => true]);
    }

    public function convertToOS($id)
    {
        try {
            DB::beginTransaction();

            $budget = Budget::with(['items', 'parts'])->findOrFail($id);

            // Cria a OS baseada no orçamento
            $os = OrdemServico::create([
                'client_id' => $budget->client_id,
                'veiculo_id' => $budget->veiculo_id,
                'status' => 'pending',
                'description' => "[Convertido do Orçamento #{$budget->id}] " . $budget->description,
                'user_id' => Auth::id(),
                'km_entry' => $budget->veiculo->km_atual ?? 0
            ]);

            // Copia itens
            foreach ($budget->items as $item) {
                OrdemServicoItem::create([
                    'ordem_servico_id' => $os->id,
                    'service_id' => $item->service_id,
                    'price' => $item->price,
                    'quantity' => $item->quantity
                ]);
            }

            // Copia peças
            foreach ($budget->parts as $part) {
                OrdemServicoPart::create([
                    'ordem_servico_id' => $os->id,
                    'inventory_item_id' => $part->inventory_item_id,
                    'price' => $part->price,
                    'quantity' => $part->quantity
                ]);
            }

            $budget->update(['status' => 'approved']);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Convertido para OS com sucesso!', 'os_id' => $os->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendWhatsApp($id)
    {
        $budget = Budget::with(['client', 'veiculo', 'items', 'parts'])->findOrFail($id);
        $phone = $budget->client->mobile ?? $budget->client->phone;
        
        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'Cliente sem telefone cadastrado.']);
        }

        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) == 11) $phone = "55" . $phone;

        $message = "Olá, " . ($budget->client->name ?? $budget->client->company_name) . "!\n";
        $message .= "Segue o orçamento para o veículo " . $budget->veiculo->modelo . " (Placa: " . $budget->veiculo->placa . "):\n\n";
        $message .= "Total: R$ " . number_format($budget->total, 2, ',', '.') . "\n";
        $message .= "Válido até: " . $budget->valid_until->format('d/m/Y') . "\n\n";
        $message .= "Podemos prosseguir com o serviço?";

        $url = "https://api.whatsapp.com/send?phone=" . $phone . "&text=" . urlencode($message);

        return response()->json(['success' => true, 'url' => $url]);
    }
}