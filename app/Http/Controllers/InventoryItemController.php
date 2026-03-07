<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $categories = \App\Models\MenuCategory::all();
        return view('content.inventory.items.index', compact('suppliers', 'categories'));
    }

    /**
     * Return data for DataTables.
     */
    public function dataBase(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $totalData = InventoryItem::where('company_id', $companyId)->count();
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

        $query = InventoryItem::with(['supplier', 'category'])->where('company_id', $companyId);

        // Filtro por tipo (Insumo ou Venda)
        if ($request->has('type')) {
            if ($request->type === 'ingredient') {
                $query->where('is_ingredient', true);
            } elseif ($request->type === 'sale') {
                $query->where('is_for_sale', true)->where('is_ingredient', false);
            }
        }

        $totalFiltered = $query->count(); // Importante: contar após o filtro de tipo

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
            $nestedData['category_name'] = $item->category ? $item->category->name : 'Geral';
            $nestedData['is_ingredient'] = $item->is_ingredient;
            $nestedData['is_for_sale'] = $item->is_for_sale;
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
            $nestedData['profit'] = '';
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
        try {
            $data = $request->all();

            // Ignore image if it's not a file
            if ($request->has('image') && !$request->hasFile('image')) {
                unset($data['image']);
            }

            // Defaults for niche-specific behavior
            $isFoodService = get_current_niche() === 'food_service';

            // is_for_sale handling (checkbox)
            if (!isset($data['is_for_sale'])) {
                $data['is_for_sale'] = 0;
            }

            $validated = Validator::make($data, [
                'name' => 'required|string|max:255',
                'sku' => 'nullable|string|max:255|unique:inventory_items,sku',
                'description' => 'nullable|string',
                'cost_price' => 'nullable|numeric|min:0',
                'selling_price' => ($isFoodService && $data['is_for_sale'] == 0) ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
                'quantity' => 'nullable|numeric|min:0',
                'min_quantity' => 'nullable|numeric|min:0',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'menu_category_id' => 'nullable|exists:menu_categories,id',
                'unit' => 'required|string|max:20',
                'location' => 'nullable|string|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'is_ingredient' => 'nullable|boolean',
                'is_for_sale' => 'nullable|boolean'
            ])->validate();

            // Default values for nullable fields
            $validated['cost_price'] = $validated['cost_price'] ?? 0;
            $validated['quantity'] = $validated['quantity'] ?? 0;
            $validated['min_quantity'] = $validated['min_quantity'] ?? 0;

            // Ensure selling_price is 0 if not for sale
            if (isset($validated['is_for_sale']) && $validated['is_for_sale'] == 0) {
                $validated['selling_price'] = $validated['selling_price'] ?? 0;
            }

            $item = InventoryItem::create($validated);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('inventory', 'public');
                $item->images()->create([
                    'company_id' => auth()->user()->company_id,
                    'path' => $path,
                    'is_main' => true
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Item criado com sucesso!', 'data' => $item]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Erro de validação', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao salvar item: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = InventoryItem::with('mainImage')->find($id);
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
        try {
            $item = InventoryItem::find($id);
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Item não encontrado.'], 404);
            }

            $data = $request->all();

            // Ignore image if it's not a file
            if ($request->has('image') && !$request->hasFile('image')) {
                unset($data['image']);
            }

            // Defaults for niche-specific behavior
            $isFoodService = get_current_niche() === 'food_service';

            // is_for_sale handling (checkbox)
            if (!isset($data['is_for_sale'])) {
                $data['is_for_sale'] = 0;
            }

            $validated = Validator::make($data, [
                'name' => 'required|string|max:255',
                'sku' => 'nullable|string|max:255|unique:inventory_items,sku,' . $id,
                'description' => 'nullable|string',
                'cost_price' => 'nullable|numeric|min:0',
                'selling_price' => ($isFoodService && $data['is_for_sale'] == 0) ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
                'quantity' => 'nullable|numeric|min:0',
                'min_quantity' => 'nullable|numeric|min:0',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'menu_category_id' => 'nullable|exists:menu_categories,id',
                'unit' => 'required|string|max:20',
                'location' => 'nullable|string|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'is_ingredient' => 'nullable|boolean',
                'is_for_sale' => 'nullable|boolean'
            ])->validate();

            // Default values for nullable fields
            $validated['cost_price'] = $validated['cost_price'] ?? 0;
            $validated['quantity'] = $validated['quantity'] ?? 0;
            $validated['min_quantity'] = $validated['min_quantity'] ?? 0;

            // Ensure selling_price is 0 if not for sale
            if (isset($validated['is_for_sale']) && $validated['is_for_sale'] == 0) {
                $validated['selling_price'] = $validated['selling_price'] ?? 0;
            }

            $item->update($validated);

            if ($request->hasFile('image')) {
                // Remove imagem antiga se existir
                $item->mainImage()?->delete();

                $path = $request->file('image')->store('inventory', 'public');
                $item->images()->create([
                    'company_id' => auth()->user()->company_id,
                    'path' => $path,
                    'is_main' => true
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Item atualizado com sucesso!', 'data' => $item]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Erro de validação', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar item: ' . $e->getMessage()], 500);
        }
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
