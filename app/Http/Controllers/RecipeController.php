<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\RecipeItem;

class RecipeController extends Controller
{
    public function index()
    {
        $products = InventoryItem::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->where('is_for_sale', true) // Produtos de venda
            ->where('is_ingredient', false) // Que não são apenas insumos
            ->withCount('ingredients') // Conta a relação correta ProductRecipe
            ->get();

        return view('content.recipes.index', compact('products'));
    }

    public function show($id)
    {
        $product = InventoryItem::with('recipe.ingredient')->findOrFail($id);
        $ingredients = InventoryItem::where('is_active', true)
            ->where('is_ingredient', true)
            ->get();

        return view('content.recipes.show', compact('product', 'ingredients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.ingredient_id' => 'required|exists:inventory_items,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.0001',
            'ingredients.*.unit' => 'required|string|max:10',
        ]);

        RecipeItem::where('inventory_item_id', $validated['inventory_item_id'])->delete();

        foreach ($validated['ingredients'] as $ingredient) {
            RecipeItem::create([
                'inventory_item_id' => $validated['inventory_item_id'],
                'ingredient_id' => $ingredient['ingredient_id'],
                'quantity' => $ingredient['quantity'],
                'unit' => $ingredient['unit'],
            ]);
        }

        return redirect()->route('recipes.show', $validated['inventory_item_id'])
            ->with('success', 'Ficha de produção salva com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'ingredients' => 'required|array|min:1',
            'ingredients.*.ingredient_id' => 'required|exists:inventory_items,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.0001',
            'ingredients.*.unit' => 'required|string|max:10',
        ]);

        RecipeItem::where('inventory_item_id', $id)->delete();

        foreach ($validated['ingredients'] as $ingredient) {
            RecipeItem::create([
                'inventory_item_id' => $id,
                'ingredient_id' => $ingredient['ingredient_id'],
                'quantity' => $ingredient['quantity'],
                'unit' => $ingredient['unit'],
            ]);
        }

        return redirect()->route('recipes.show', $id)
            ->with('success', 'Ficha de produção atualizada!');
    }

    public function destroy($id, $ingredientId)
    {
        RecipeItem::where('inventory_item_id', $id)
            ->where('ingredient_id', $ingredientId)
            ->delete();

        return response()->json(['success' => true]);
    }
}
