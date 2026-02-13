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
            $hasPhone = $item->client && ($item->client->phone || $item->client->whatsapp);
            $data[] = [
                'id' => $item->id,
                'client_id' => $item->client_id,
                'has_phone' => $hasPhone,
                'client' => $item->client ? ($item->client->name ?? $item->client->company_name) : '-',
                'vehicle' => $item->veiculo ? "{$item->veiculo->placa} - {$item->veiculo->modelo}" : '-',
                'status' => $item->status,
                'total' => $item->total,
                'date' => $item->created_at->format('d/m/Y'),
                'valid_until' => $item->valid_until ? $item->valid_until->format('d/m/Y') : '-'
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data
        ]);
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
            'veiculo_id' => 'required|exists:veiculos,id',
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

            // DISPARAR NOTIFICAÇÃO PUSH REAL
            $user = Auth::user();
            if ($user && $user->expo_push_token) {
                \App\Helpers\Helpers::sendExpoNotification(
                    $user->expo_push_token,
                    "Novo Orçamento Gerado! 📄",
                    "O orçamento para o veículo " . ($budget->veiculo->modelo ?? '') . " foi criado com sucesso."
                );
            }

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

            // Notificar o dono da oficina sobre a aprovação
            $user = Auth::user();
            if ($user && $user->expo_push_token) {
                \App\Helpers\Helpers::sendExpoNotification(
                    $user->expo_push_token,
                    "Orçamento Aprovado! ✅",
                    "A OS #{$os->id} foi gerada e o serviço já pode ser iniciado."
                );
            }

            return response()->json(['success' => true, 'message' => 'Convertido para OS com sucesso!', 'os_id' => $os->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendWhatsApp($id)
    {
        $budget = Budget::with(['client', 'veiculo', 'items', 'parts'])->findOrFail($id);
        // Tenta primeiro a coluna 'whatsapp', depois 'phone'
        $phone = $budget->client->whatsapp ?? $budget->client->phone;
        
        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'Cliente sem telefone cadastrado (campos whatsapp e phone vazios).']);
        }

        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) == 11) $phone = "55" . $phone;

        $message = "Olá, " . ($budget->client->name ?? $budget->client->company_name) . "!\n";
        $message .= "Segue o orçamento para o veículo " . $budget->veiculo->modelo . " (Placa: " . $budget->veiculo->placa . "):\n\n";
        $message .= "Total: R$ " . number_format($budget->total, 2, ',', '.') . "\n";
        $message .= "Válido até: " . ($budget->valid_until ? $budget->valid_until->format('d/m/Y') : 'N/A') . "\n\n";
        $message .= "Você pode visualizar os detalhes e aprovar online através do link abaixo:\n";
        $message .= route('public.budget.show', $budget->uuid) . "\n\n";
        $message .= "Podemos prosseguir com o serviço?";

        $url = "https://api.whatsapp.com/send?phone=" . $phone . "&text=" . urlencode($message);

        return response()->json(['success' => true, 'url' => $url]);
    }

    

        public function quickView($id)

        {

            $budget = Budget::with(['client', 'veiculo', 'items.service', 'parts.part'])->findOrFail($id);

            

            $html = '<div class="row mb-3">

                        <div class="col-6"><strong>Cliente:</strong><br>'.$budget->client->name.'</div>

                        <div class="col-6 text-end"><strong>Veículo:</strong><br>'.$budget->veiculo->marca.' '.$budget->veiculo->modelo.' ('.$budget->veiculo->placa.')</div>

                     </div>';

            

            $html .= '<table class="table table-sm border">

                        <thead class="table-light"><tr><th>Item</th><th class="text-center">Qtd</th><th class="text-end">Valor</th></tr></thead>

                        <tbody>';

            

            foreach($budget->items as $item) {

                $html .= '<tr><td>'.$item->service->name.'</td><td class="text-center">'.$item->quantity.'</td><td class="text-end">R$ '.number_format($item->price, 2, ',', '.').'</td></tr>';

            }

            foreach($budget->parts as $part) {

                $html .= '<tr><td>'.$part->part->name.' (Peça)</td><td class="text-center">'.$part->quantity.'</td><td class="text-end">R$ '.number_format($part->price, 2, ',', '.').'</td></tr>';

            }

            

            $html .= '</tbody><tfoot class="table-light"><tr><th colspan="2" class="text-end">Total:</th><th class="text-end">R$ '.number_format($budget->total, 2, ',', '.').'</th></tr></tfoot></table>';

            

            if($budget->description) {

                $html .= '<div class="mt-3 small text-muted"><strong>Obs:</strong> '.$budget->description.'</div>';

            }

    

            return response($html);

        }

    }

    