<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Lista itens abaixo do estoque mínimo e pedidos recentes
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;

        // Itens que precisam de reposição
        $lowStockItems = InventoryItem::where('company_id', $companyId)
            ->whereRaw('quantity <= min_quantity')
            ->with('supplier')
            ->get();

        // Pedidos de compra recentes
        $orders = PurchaseOrder::where('company_id', $companyId)
            ->with('supplier')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('content.inventory.purchase-orders', compact('lowStockItems', 'orders'));
    }

    /**
     * Gera pedidos de compra automáticos baseados no estoque baixo
     */
    public function generateAutomaticOrders()
    {
        $companyId = Auth::user()->company_id;

        $items = InventoryItem::where('company_id', $companyId)
            ->whereRaw('quantity <= min_quantity')
            ->whereNotNull('supplier_id')
            ->get();

        if ($items->isEmpty()) {
            return back()->with('info', 'Não há itens com estoque baixo para reposição agora.');
        }

        // Agrupa os itens por fornecedor
        $groupedBySupplier = $items->groupBy('supplier_id');

        DB::transaction(function () use ($groupedBySupplier, $companyId) {
            foreach ($groupedBySupplier as $supplierId => $itemsToBuy) {
                // Cria o pedido de compra para este fornecedor
                $order = PurchaseOrder::create([
                    'company_id' => $companyId,
                    'supplier_id' => $supplierId,
                    'status' => 'draft',
                    'notes' => 'Gerado automaticamente pelo sistema Ghotme ERP para reposição de estoque.',
                ]);

                $total = 0;
                foreach ($itemsToBuy as $item) {
                    $qtyToBuy = ($item->min_quantity * 2) - $item->quantity; // Sugestão: comprar o dobro do mínimo
                    if ($qtyToBuy <= 0) $qtyToBuy = $item->min_quantity;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $order->id,
                        'inventory_item_id' => $item->id,
                        'quantity' => $qtyToBuy,
                        'unit_cost' => $item->cost_price,
                    ]);

                    $total += $qtyToBuy * $item->cost_price;
                }

                $order->update(['total_amount' => $total]);
            }
        });

        return back()->with('success', 'Pedidos de compra gerados com sucesso!');
    }

    /**
     * Recebe o pedido e atualiza o estoque (Finalização)
     */
    public function receive($id)
    {
        $order = PurchaseOrder::where('id', $id)
            ->where('company_id', Auth::user()->company_id)
            ->with('items')
            ->firstOrFail();

        if ($order->status === 'received') {
            return back()->with('error', 'Este pedido já foi recebido.');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $inventoryItem = $item->inventoryItem;
                $inventoryItem->increment('quantity', $item->quantity);
            }

            $order->update([
                'status' => 'received',
                'received_at' => now(),
            ]);
        });

        return back()->with('success', 'Estoque atualizado e pedido marcado como recebido!');
    }
}
