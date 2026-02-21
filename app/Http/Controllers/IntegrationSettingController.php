<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IntegrationSetting;

class IntegrationSettingController extends Controller
{
    public function index()
    {
        $settings = IntegrationSetting::first() ?? new IntegrationSetting();
        return view('content.settings.integrations.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = IntegrationSetting::first() ?? new IntegrationSetting();
        
        $validated = $request->validate([
            'asaas_api_key' => 'nullable|string|max:255',
            'asaas_environment' => 'required|in:sandbox,production',
            'whatsapp_token' => 'nullable|string|max:255',
            'whatsapp_phone_number_id' => 'nullable|string|max:255',
            'fiscal_api_token' => 'nullable|string|max:255',
            'fiscal_environment' => 'required|in:sandbox,production',
        ]);

        $settings->fill($validated);
        $settings->save();

        return response()->json(['success' => true, 'message' => 'Integrações atualizadas com sucesso!']);
    }
}