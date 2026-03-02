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
            'icon' => $request->icon ?? 'ti-tools-kitchen-2'
        ]);

        return back()->with('success', 'Categoria criada com sucesso!');
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
}
