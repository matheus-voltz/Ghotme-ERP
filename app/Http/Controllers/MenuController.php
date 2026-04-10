<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;

        $categories = MenuCategory::with('items.images')
            ->where('company_id', $companyId)
            ->orderBy('order')
            ->get();

        $unassignedItems = InventoryItem::with('images')
            ->where('company_id', $companyId)
            ->whereNull('menu_category_id')
            ->get();

        return view('content.menu.manage', compact('categories', 'unassignedItems'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:product,ingredient,beverage',
            'icon' => 'nullable|string'
        ]);

        MenuCategory::create([
            'company_id' => Auth::user()->company_id,
            'name' => $request->name,
            'type' => $request->type,
            'icon' => $request->icon ?? 'ti-tools-kitchen-2',
            'order' => MenuCategory::where('company_id', Auth::user()->company_id)->count()
        ]);

        return back()->with('success', 'Categoria criada com sucesso!');
    }

    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:product,ingredient,beverage',
            'icon' => 'nullable|string'
        ]);

        $category = MenuCategory::where('company_id', Auth::user()->company_id)->findOrFail($id);
        $category->update([
            'name' => $request->name,
            'type' => $request->type,
            'icon' => $request->icon
        ]);

        return back()->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroyCategory($id)
    {
        $category = MenuCategory::where('company_id', Auth::user()->company_id)->findOrFail($id);

        // Remove associação dos itens
        InventoryItem::where('menu_category_id', $category->id)->update(['menu_category_id' => null]);

        $category->delete();

        return back()->with('success', 'Categoria removida com sucesso!');
    }

    public function assignItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'category_id' => 'nullable|exists:menu_categories,id',
            'is_ingredient' => 'nullable|boolean'
        ]);

        $item = InventoryItem::findOrFail($request->item_id);
        $item->update([
            'menu_category_id' => $request->category_id,
            'is_ingredient' => $request->is_ingredient ?? false
        ]);

        return response()->json(['success' => true]);
    }

    public function updateTheme(Request $request)
    {
        $request->validate([
            'primary_color' => 'required|string|max:10'
        ]);

        $company = Auth::user()->company;

        if (!$company) {
            return back()->withErrors(['error' => 'Empresa não encontrada para este usuário.']);
        }

        $config = $company->configuracoes_net ?? [];
        $config['public_menu_theme'] = $request->primary_color;

        $company->configuracoes_net = $config;
        $company->save();

        return back()->with('success', 'Aparência do cardápio atualizada com sucesso!');
    }
}
