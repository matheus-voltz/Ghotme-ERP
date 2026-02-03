<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;

class InventoryAlertController extends Controller
{
    /**
     * Exibe a página de estoque crítico.
     */
    public function index()
    {
        return view('content.inventory.alerts.index');
    }

    /**
     * Retorna dados filtrados para o DataTables (apenas itens abaixo ou no limite).
     */
    public function dataBase(Request $request)
    {
        // Query base: itens onde quantidade é menor ou igual ao estoque mínimo
        // E incluímos um filtro de "alerta" (até 20% acima do mínimo) se desejar, 
        // mas o padrão crítico é <= min_quantity.
        $query = InventoryItem::whereColumn('quantity', '<=', 'min_quantity')
            ->where('is_active', true);

        $totalData = $query->count();
        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = 'quantity';
        $dir = 'asc'; // Mostrar o que está mais em falta primeiro

        $items = $query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->with('supplier')
            ->get();

        $data = [];
        $ids = $start;

        foreach ($items as $item) {
            $nestedData['fake_id'] = ++$ids;
            $nestedData['id'] = $item->id;
            $nestedData['name'] = $item->name;
            $nestedData['sku'] = $item->sku;
            $nestedData['quantity'] = $item->quantity;
            $nestedData['min_quantity'] = $item->min_quantity;
            $nestedData['supplier_name'] = $item->supplier ? $item->supplier->name : '-';
            $nestedData['status'] = $item->quantity <= 0 ? 'Zerado' : 'Crítico';
            
            $data[] = $nestedData;
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => intval($totalData),
            'recordsFiltered' => intval($totalFiltered),
            'data' => $data
        ]);
    }
}
