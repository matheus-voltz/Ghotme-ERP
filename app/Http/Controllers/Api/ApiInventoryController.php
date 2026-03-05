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
            $query->where(function ($q) use ($search) {
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

    /**
     * Cardápio visual: produtos agrupados por categoria com foto principal.
     * Criado para o PDV Mobile (food_service).
     */
    public function menu()
    {
        $user = Auth::user();

        // Categorias ativas com seus itens
        $categories = \App\Models\MenuCategory::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function ($cat) {
                $items = InventoryItem::where('menu_category_id', $cat->id)
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->where('is_ingredient', false)->orWhereNull('is_ingredient');
                    })
                    ->with(['mainImage', 'ingredients.ingredient'])
                    ->get()
                    ->map(function ($item) {
                        $baseUrl = config('app.url');
                        $imageUrl = null;
                        if ($item->mainImage) {
                            $imageUrl = $baseUrl . '/storage/' . $item->mainImage->path;
                        }
                        $ingredientsList = $item->ingredients->map(fn($r) => [
                            'name' => $r->ingredient->name ?? 'Item',
                            'qty' => $r->quantity,
                        ])->toArray();
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'description' => $item->description,
                            'selling_price' => (float) $item->selling_price,
                            'quantity' => $item->quantity,
                            'image_url' => $imageUrl,
                            'ingredients' => $ingredientsList,
                        ];
                    });

                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'icon' => $cat->icon,
                    'items' => $items,
                ];
            })
            ->filter(fn($cat) => $cat['items']->isNotEmpty())
            ->values();

        // Itens sem categoria
        $uncategorized = InventoryItem::where('company_id', $user->company_id)
            ->whereNull('menu_category_id')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('is_ingredient', false)->orWhereNull('is_ingredient');
            })
            ->with(['mainImage', 'ingredients.ingredient'])
            ->get()
            ->map(function ($item) {
                $baseUrl = config('app.url');
                $imageUrl = null;
                if ($item->mainImage) {
                    $imageUrl = $baseUrl . '/storage/' . $item->mainImage->path;
                }
                $ingredientsList = $item->ingredients->map(fn($r) => [
                    'name' => $r->ingredient->name ?? 'Item',
                    'qty' => $r->quantity,
                ])->toArray();
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'selling_price' => (float) $item->selling_price,
                    'quantity' => $item->quantity,
                    'image_url' => $imageUrl,
                    'ingredients' => $ingredientsList,
                ];
            });

        if ($uncategorized->isNotEmpty()) {
            $categories->push([
                'id' => 0,
                'name' => 'Outros',
                'icon' => 'cube-outline',
                'items' => $uncategorized,
            ]);
        }

        return response()->json($categories);
    }
}
