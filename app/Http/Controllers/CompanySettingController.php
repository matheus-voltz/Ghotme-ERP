<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    public function index()
    {
        $settings = CompanySetting::first() ?? new CompanySetting();
        return view('content.settings.company-data.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = CompanySetting::first() ?? new CompanySetting();

        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'cnpj' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'zip_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $path = $request->file('logo')->store('company', 'public');
            $validated['logo_path'] = $path;
        }

        $settings->fill($validated);
        $settings->save();

        return response()->json(['success' => true, 'message' => 'Dados da empresa atualizados com sucesso!']);
    }
}