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
        $query = InventoryItem::with(['mainImage', 'category']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(20);

        // Transformar para incluir a URL da imagem
        $items->getCollection()->transform(function ($item) {
            $baseUrl = config('app.url');
            $item->image_url = $item->mainImage ? $baseUrl . '/storage/' . $item->mainImage->path : null;
            return $item;
        });

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => [
                'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('inventory_items')->where(function ($query) {
                    return $query->where('company_id', Auth::user()->company_id);
                })
            ],
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'menu_category_id' => 'nullable|exists:menu_categories,id',
            'image_base64' => 'nullable|string'
        ]);

        $item = InventoryItem::create([
            'company_id' => Auth::user()->company_id,
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'cost_price' => $validated['cost_price'] ?? 0,
            'selling_price' => $validated['selling_price'],
            'quantity' => $validated['quantity'],
            'min_quantity' => $validated['min_quantity'] ?? 5,
            'menu_category_id' => $validated['menu_category_id'] ?? null,
            'unit' => 'un',
            'is_active' => true,
        ]);

        // Processar Imagem se enviada
        if (!empty($validated['image_base64'])) {
            try {
                $base64Image = $validated['image_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, etc

                    if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                        throw new \Exception('invalid image type');
                    }
                    $base64Image = str_replace(' ', '+', $base64Image);
                    $contents = base64_decode($base64Image);

                    if ($contents) {
                        $fileName = 'inventory/' . uniqid() . '.' . $type;
                        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $contents);

                        $item->images()->create([
                            'company_id' => Auth::user()->company_id,
                            'path' => $fileName,
                            'is_main' => true,
                            'order' => 0
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Erro ao salvar imagem do item: " . $e->getMessage());
            }
        }

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
                            'sku' => $item->sku,
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
                    'sku' => $item->sku,
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
    public function show($id)
    {
        $item = InventoryItem::with(['mainImage', 'category'])->findOrFail($id);
        $baseUrl = config('app.url');
        $item->image_url = $item->mainImage ? $baseUrl . '/storage/' . $item->mainImage->path : null;
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'sku' => [
                'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('inventory_items')->ignore($id)->where(function ($query) {
                    return $query->where('company_id', Auth::user()->company_id);
                })
            ],
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'menu_category_id' => 'nullable|exists:menu_categories,id',
            'image_base64' => 'nullable|string'
        ]);

        $item->update([
            'name' => $validated['name'] ?? $item->name,
            'sku' => $validated['sku'] ?? $item->sku,
            'cost_price' => $validated['cost_price'] ?? $item->cost_price,
            'selling_price' => $validated['selling_price'] ?? $item->selling_price,
            'quantity' => $validated['quantity'] ?? $item->quantity,
            'min_quantity' => $validated['min_quantity'] ?? $item->min_quantity,
            'menu_category_id' => $validated['menu_category_id'] ?? $item->menu_category_id,
        ]);

        // Processar Imagem se enviada
        if (!empty($validated['image_base64'])) {
            try {
                $base64Image = $validated['image_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                    $type = strtolower($type[1]);

                    if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                        throw new \Exception('invalid image type');
                    }
                    $base64Image = str_replace(' ', '+', $base64Image);
                    $contents = base64_decode($base64Image);

                    if ($contents) {
                        $fileName = 'inventory/' . uniqid() . '.' . $type;
                        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $contents);

                        // Marcar anteriores como não principais
                        $item->images()->update(['is_main' => false]);

                        $item->images()->create([
                            'company_id' => Auth::user()->company_id,
                            'path' => $fileName,
                            'is_main' => true,
                            'order' => 0
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Erro ao atualizar imagem do item: " . $e->getMessage());
            }
        }

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy($id)
    {
        $item = InventoryItem::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true]);
    }
}
