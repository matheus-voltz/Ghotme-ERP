<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IntegrationSetting;

use Illuminate\Support\Facades\Auth;

class IntegrationSettingController extends Controller
{
    public function index()
    {
        $settings = IntegrationSetting::where('company_id', Auth::user()->company_id)->first() ?? new IntegrationSetting();
        return view('content.settings.integrations.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = IntegrationSetting::where('company_id', Auth::user()->company_id)->first();
        
        if (!$settings) {
            $settings = new IntegrationSetting();
            $settings->company_id = Auth::user()->company_id;
        }

        $validated = $request->validate([
            'active_payment_gateway' => 'required|string',
            'asaas_api_key' => 'nullable|string|max:255',
            'asaas_environment' => 'nullable|string',
            'mercado_pago_public_key' => 'nullable|string|max:255',
            'mercado_pago_access_token' => 'nullable|string|max:255',
            'pagar_me_api_key' => 'nullable|string|max:255',
            'pagar_me_encryption_key' => 'nullable|string|max:255',
            'pagar_me_environment' => 'nullable|string',
            'pagbank_token' => 'nullable|string|max:255',
            'pagbank_environment' => 'nullable|string',
            'stripe_public_key' => 'nullable|string|max:255',
            'stripe_secret_key' => 'nullable|string|max:255',
            'stripe_webhook_secret' => 'nullable|string|max:255',
            'bitcoin_api_key' => 'nullable|string|max:255',
            'bitcoin_webhook_secret' => 'nullable|string|max:255',
            'whatsapp_api_url' => 'nullable|string|max:255',
            'whatsapp_instance_id' => 'nullable|string|max:255',
            'whatsapp_api_key' => 'nullable|string|max:255',
            'whatsapp_token' => 'nullable|string|max:255',
            'whatsapp_phone_number_id' => 'nullable|string|max:255',
            'fiscal_api_token' => 'nullable|string|max:255',
            'fiscal_environment' => 'nullable|string',
        ]);

        $settings->fill($validated);
        $settings->save();

        return response()->json(['success' => true, 'message' => 'Integrações atualizadas com sucesso!']);
    }
}
