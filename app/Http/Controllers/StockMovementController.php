<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    /**
     * Exibe a página de Entradas
     */
    public function stockIn()
    {
        $items = InventoryItem::where('is_active', true)->get();
        return view('content.inventory.movements.stock-in', compact('items'));
    }

    /**
     * Exibe a página de Saídas
     */
    public function stockOut()
    {
        $items = InventoryItem::where('is_active', true)->get();
        return view('content.inventory.movements.stock-out', compact('items'));
    }

    public function stockAdjustment()
    {
        $items = InventoryItem::where('is_active', true)->get();
        return view('content.inventory.movements.stock-adjustment', compact('items'));
    }

    /**
     * Processa a movimentação de estoque
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required_unless:type,adjustment|integer|min:1',
            'new_quantity' => 'required_if:type,adjustment|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:255',
            'reason_base' => 'nullable|string',
            'reason_detail' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $item = InventoryItem::lockForUpdate()->find($validated['inventory_item_id']);
            $type = $validated['type'];
            $qty = $validated['quantity'] ?? 0;
            $reason = $validated['reason'] ?? ($validated['reason_base'] . ' ' . ($validated['reason_detail'] ?? ''));

            // Lógica específica para Ajuste
            if ($type === 'adjustment') {
                $newQty = $validated['new_quantity'];
                $diff = $newQty - $item->quantity;
                
                if ($diff === 0) {
                    return response()->json(['success' => true, 'message' => 'Nenhuma alteração necessária.']);
                }

                $qty = abs($diff);
                // Registramos o movimento de ajuste como 'in' ou 'out' internamente para manter o histórico claro
                // mas guardamos o tipo 'adjustment' na tabela para auditoria se desejar.
                // Aqui vou manter 'adjustment' na tabela.
                $item->quantity = $newQty;
            } else {
                // Se for saída, verifica se tem estoque suficiente
                if ($type === 'out' && $item->quantity < $qty) {
                    return response()->json(['success' => false, 'message' => 'Estoque insuficiente!'], 422);
                }

                if ($type === 'in') {
                    $item->quantity += $qty;
                    if (!empty($validated['unit_price'])) {
                        $item->cost_price = $validated['unit_price'];
                    }
                } else {
                    $item->quantity -= $qty;
                }
            }

            // Cria o registro da movimentação
            StockMovement::create([
                'inventory_item_id' => $validated['inventory_item_id'],
                'type' => $type,
                'quantity' => $qty,
                'unit_price' => $validated['unit_price'] ?? ($type === 'in' ? $item->cost_price : $item->selling_price),
                'reason' => $reason,
                'user_id' => Auth::id(),
            ]);

            $item->save();

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Estoque atualizado com sucesso!', 
                'new_quantity' => $item->quantity
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro ao processar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Histórico de movimentações (JSON para DataTables se necessário)
     */
    public function history(Request $request)
    {
        $movements = StockMovement::with(['item', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
            
        return response()->json($movements);
    }
}