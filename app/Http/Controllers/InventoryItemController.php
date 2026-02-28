<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\Supplier;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        return view('content.inventory.items.index', compact('suppliers'));
    }

    /**
     * Return data for DataTables.
     */
    public function dataBase(Request $request)
    {
        $totalData = InventoryItem::count();
        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');

        $columns = [
            0 => 'id',
            1 => 'id',
            2 => 'name',
            3 => 'sku',
            4 => 'quantity',
            5 => 'selling_price',
            6 => 'location',
            7 => 'is_active',
            8 => 'id'
        ];
        $orderColumnIndex = $request->input('order.0.column');
        $order = $columns[$orderColumnIndex] ?? 'id';
        $dir = $request->input('order.0.dir') ?? 'desc';

        $query = InventoryItem::with('supplier');

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
            $totalFiltered = $query->count();
        }

        $items = $query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
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
            $nestedData['cost_price'] = $item->cost_price;
            $nestedData['selling_price'] = $item->selling_price;
            $nestedData['location'] = $item->location;
            $nestedData['is_active'] = $item->is_active;
            $nestedData['supplier_name'] = $item->supplier ? $item->supplier->name : '-';
            $nestedData['supplier_id'] = $item->supplier_id;
            $nestedData['unit'] = $item->unit;
            $nestedData['description'] = $item->description;
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255|unique:inventory_items,sku',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:20',
            'location' => 'nullable|string|max:100',
        ]);

        $item = InventoryItem::create($validated);

        return response()->json(['success' => true, 'message' => 'Item criado com sucesso!', 'data' => $item]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = InventoryItem::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item não encontrado.'], 404);
        }
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $item = InventoryItem::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item não encontrado.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255|unique:inventory_items,sku,' . $id,
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:20',
            'location' => 'nullable|string|max:100',
        ]);

        $item->update($validated);

        return response()->json(['success' => true, 'message' => 'Item atualizado com sucesso!', 'data' => $item]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = InventoryItem::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item não encontrado.'], 404);
        }

        $item->delete();

        return response()->json(['success' => true, 'message' => 'Item removido com sucesso!']);
    }

    public function printLabel($id)
    {
        $item = InventoryItem::findOrFail($id);
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . ($item->sku ?? $item->id);

        return view('content.inventory.items.print-label', compact('item', 'qrCodeUrl'));
    }
}
