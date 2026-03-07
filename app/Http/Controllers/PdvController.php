<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\InventoryItem;
use App\Models\Clients;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PdvController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;
        
        $categories = MenuCategory::where('company_id', $companyId)
            ->where('is_active', true)
            ->whereHas('items', function($q) {
                $q->where('is_ingredient', false)->where('is_active', true);
            })
            ->with(['items' => function($q) {
                $q->where('is_ingredient', false)->where('is_active', true);
            }, 'items.images'])
            ->orderBy('order')
            ->get();

        $clients = Clients::where('company_id', $companyId)->get();
        $paymentMethods = PaymentMethod::where(function($q) use ($companyId) {
                $q->where('company_id', $companyId)
                  ->orWhereNull('company_id');
            })
            ->where('is_active', true)
            ->get();

        return view('content.menu.pos', compact('categories', 'clients', 'paymentMethods'));
    }
}
