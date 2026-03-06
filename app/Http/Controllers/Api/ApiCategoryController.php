<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MenuCategory;
use Illuminate\Support\Facades\Auth;

class ApiCategoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->company_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $categories = MenuCategory::where('company_id', $user->company_id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->company_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:50',
        ]);

        $category = MenuCategory::create([
            'company_id' => $user->company_id,
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? 'cube-outline',
            'type' => $validated['type'] ?? 'product',
            'is_active' => true,
            'order' => MenuCategory::where('company_id', $user->company_id)->count() + 1
        ]);

        return response()->json($category, 201);
    }
}
