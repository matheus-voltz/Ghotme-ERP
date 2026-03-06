<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashRegister;
use App\Models\CashRegisterMovement;
use App\Models\Service;
use App\Models\OrdemServico;
use App\Models\OrdemServicoItem;
use App\Models\OrdemServicoItemAddon;
use App\Models\ServiceAddon;
use App\Models\FinancialTransaction;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashRegisterController extends Controller
{
    public function index()
    {
        $currentRegister = CashRegister::where('status', 'open')->first();
        $history = CashRegister::where('status', 'closed')
            ->with('user')
            ->orderBy('closed_at', 'desc')
            ->limit(30)
            ->get();

        $services = collect();
        $categories = collect();

        // Se o caixa estiver aberto, carrega dados do PDV (Serviços)
        if ($currentRegister) {
            $services = Service::where('is_active', true)->with('mainImage')->get();
        }

        return view('content.cash-register.index', compact('currentRegister', 'history', 'services'));
    }

    public function open(Request $request)
    {
        $validated = $request->validate([
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $existing = CashRegister::where('status', 'open')->first();
        if ($existing) {
            return back()->with('error', 'Já existe um caixa aberto! Feche-o antes de abrir outro.');
        }

        CashRegister::create([
            'user_id' => Auth::id(),
            'opened_at' => now(),
            'opening_balance' => $validated['opening_balance'],
            'status' => 'open',
        ]);

        return redirect()->route('cash-register.index')->with('success', 'Caixa aberto com sucesso!');
    }

    public function close(Request $request, $id)
    {
        $validated = $request->validate([
            'actual_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $register = CashRegister::where('status', 'open')->findOrFail($id);
        $expected = $register->calculated_balance;

        $register->update([
            'closed_at' => now(),
            'expected_balance' => $expected,
            'actual_balance' => $validated['actual_balance'],
            'difference' => $validated['actual_balance'] - $expected,
            'status' => 'closed',
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('cash-register.index')->with('success', 'Caixa fechado com sucesso!');
    }

    public function addMovement(Request $request, $id)
    {
        $validated = $request->validate([
            'type' => 'required|in:sangria,suprimento',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $register = CashRegister::where('status', 'open')->findOrFail($id);

        CashRegisterMovement::create([
            'cash_register_id' => $register->id,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'user_id' => Auth::id(),
        ]);

        $typeLabel = $validated['type'] === 'sangria' ? 'Sangria' : 'Suprimento';
        return back()->with('success', "{$typeLabel} registrado(a) com sucesso!");
    }

    public function show($id)
    {
        $register = CashRegister::with(['movements.user', 'user'])->findOrFail($id);
        return view('content.cash-register.show', compact('register'));
    }

    public function current()
    {
        $register = CashRegister::where('status', 'open')->first();
        return response()->json($register);
    }

    public function checkout(Request $request, $id)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:services,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.addons' => 'nullable|array',
            'payment_method' => 'required|string',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $register = CashRegister::where('status', 'open')->findOrFail($id);

        DB::beginTransaction();
        try {
            $totalAmount = 0;

            // 1. Criar a Ordem de Serviço (simplificada para Barraquinha)
            $os = OrdemServico::create([
                'company_id' => auth()->user()->company_id,
                'client_id' => $validated['client_id'] ?? null,
                'status' => 'finalizado',
                'description' => 'Venda Rápida (PDV)',
                'discount' => 0,
                'total_amount' => 0, // será atualizado
                'created_by' => auth()->id(),
            ]);

            // 2. Adicionar Itens e Baixar Estoque
            foreach ($validated['items'] as $itemData) {
                $service = Service::with('ingredients.inventoryItem')->findOrFail($itemData['id']);

                // Calcular total do item + addons se houver
                $unitPrice = $service->price;
                $addons = [];
                if (isset($itemData['addons']) && is_array($itemData['addons'])) {
                    foreach ($itemData['addons'] as $addonId) {
                        $addon = ServiceAddon::find($addonId);
                        if ($addon) {
                            $unitPrice += $addon->price;
                            $addons[] = $addon;
                        }
                    }
                }

                $itemTotal = $unitPrice * $itemData['qty'];
                $totalAmount += $itemTotal;

                $osItem = OrdemServicoItem::create([
                    'ordem_servico_id' => $os->id,
                    'service_id' => $service->id,
                    'quantity' => $itemData['qty'],
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotal,
                ]);

                // Registrar os addons comprados atrelados ao item da venda!
                if (!empty($addons)) {
                    foreach ($addons as $addonObj) {
                        OrdemServicoItemAddon::create([
                            'ordem_servico_item_id' => $osItem->id,
                            'service_addon_id' => $addonObj->id,
                            'name' => $addonObj->name,
                            'price' => $addonObj->price,
                        ]);
                    }
                }

                // Baixar Estoque da Ficha Técnica
                foreach ($service->ingredients as $ingredient) {
                    $itemEstoque = $ingredient->inventoryItem;
                    if ($itemEstoque) {
                        $baixaQty = $ingredient->quantity * $itemData['qty'];
                        // Atualiza saldo final
                        $itemEstoque->decrement('quantity', $baixaQty);

                        // Registra Movimento
                        InventoryMovement::create([
                            'company_id' => auth()->user()->company_id,
                            'inventory_item_id' => $itemEstoque->id,
                            'type' => 'out',
                            'quantity' => $baixaQty,
                            'date' => now(),
                            'notes' => "Baixa Automática (Ficha Técnica) - Venda PDV #{$os->id} - Serviço: {$service->name}",
                        ]);
                    }
                }
            }

            $os->update(['total_amount' => $totalAmount]);

            // 3. Registrar Movimentação no Caixa
            CashRegisterMovement::create([
                'company_id' => auth()->user()->company_id,
                'cash_register_id' => $register->id,
                'type' => 'sale',
                'amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'description' => 'Venda PDV #' . $os->id,
                'related_type' => OrdemServico::class,
                'related_id' => $os->id,
                'user_id' => auth()->id()
            ]);

            // 4. (Opcional) Gerar Transação Financeira Principal para histórico longo (se o Fiado for ativado depois, ficará pendente)
            FinancialTransaction::create([
                'company_id' => auth()->user()->company_id,
                'client_id' => $validated['client_id'] ?? null,
                'type' => 'revenue',
                'amount' => $totalAmount,
                'description' => 'Venda PDV #' . $os->id,
                'category' => 'Vendas',
                'payment_method_id' => 1, // Mapear adequadamente
                'status' => 'received', // Se for dinheiro/pix/cartão direto
                'issue_date' => now(),
                'due_date' => now(),
                'payment_date' => now()
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Venda finalizada com sucesso!', 'os_id' => $os->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro ao finalizar venda: ' . $e->getMessage()], 500);
        }
    }
}
