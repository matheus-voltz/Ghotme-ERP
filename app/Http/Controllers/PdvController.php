<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\InventoryItem;
use App\Models\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PdvController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;
        
        $categories = MenuCategory::with(['items' => function($q) {
                $q->where('is_ingredient', false); // No PDV mostramos apenas os produtos prontos
            }, 'items.images'])
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $clients = Clients::where('company_id', $companyId)->get();

        return view('content.menu.pos', compact('categories', 'clients'));
    }
}
