<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class PublicMenuController extends Controller
{
    public function show($slug)
    {
        $company = Company::where('slug', $slug)->firstOrFail();

        $categories = MenuCategory::where('company_id', $company->id)
            ->where('is_active', true)
            ->whereHas('items', function ($q) {
                $q->where('is_active', true)->where('is_ingredient', false);
            })
            ->with(['items' => function ($q) {
                $q->where('is_active', true)->where('is_ingredient', false)->orderBy('name');
            }, 'items.images'])
            ->orderBy('order')
            ->get();

        return view('content.menu.public', [
            'company' => $company,
            'categories' => $categories,
            'isMenu' => false,
            'isNavbar' => false,
            'isPublic' => true,
            'customizerHidden' => 'customizer-hide'
        ]);
    }
}
