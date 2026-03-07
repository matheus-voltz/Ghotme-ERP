<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class FoodServiceSettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        
        if (!$company) {
            return redirect()->back()->with('error', 'Empresa não encontrada.');
        }

        $settings = $company->configuracoes_net ?? [];
        
        return view('content.settings.food-service', compact('settings'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);

        if (!$company) {
            return redirect()->back()->with('error', 'Empresa não encontrada.');
        }

        $currentSettings = $company->configuracoes_net ?? [];
        
        $newSettings = $request->only([
            'printer_name', 
            'paper_width', 
            'auto_print', 
            'print_kitchen_order',
            'kitchen_printer_name'
        ]);

        $company->configuracoes_net = array_merge($currentSettings, $newSettings);
        $company->save();

        return redirect()->back()->with('success', 'Configurações salvas com sucesso!');
    }
}
