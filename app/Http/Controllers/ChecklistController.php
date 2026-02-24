<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChecklistItem;

class ChecklistController extends Controller
{
    public function index()
    {
        return view('content.settings.custom-checklist.index');
    }

    public function dataBase()
    {
        $nicheCategories = array_keys(niche('checklist_categories') ?? []);
        $nicheCategories[] = 'Geral';

        $items = ChecklistItem::whereIn('category', $nicheCategories)
            ->orderBy('category')
            ->orderBy('order')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'order' => 'nullable|integer',
        ]);

        ChecklistItem::create($validated);
        return response()->json(['success' => true, 'message' => 'Item de checklist adicionado!']);
    }

    public function destroy($id)
    {
        ChecklistItem::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
