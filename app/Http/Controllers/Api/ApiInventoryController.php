<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiInventoryController extends Controller
{
    public function index(Request $request)
    {
        // O isolamento por company_id é feito automaticamente pela trait no model InventoryItem
        $query = InventoryItem::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(20);

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
        ]);

        $item = InventoryItem::create([
            'company_id' => Auth::user()->company_id,
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'cost_price' => $validated['cost_price'] ?? 0,
            'selling_price' => $validated['selling_price'],
            'quantity' => $validated['quantity'],
            'min_quantity' => $validated['min_quantity'] ?? 5,
            'unit' => 'un',
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'data' => $item], 201);
    }
}
