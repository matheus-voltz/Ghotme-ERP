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
        $user = auth()->user();

        // Se for um registro novo (vazio), tenta pré-preencher com os dados do cadastro da Company
        if (!$settings->exists && $user->company) {
            $settings->company_name = $user->company->name;
            $settings->cnpj = $user->company->document_number;
            $settings->email = $user->company->email;
            $settings->phone = $user->company->phone;
            $settings->address = $user->company->address;
            $settings->city = $user->company->city;
            $settings->state = $user->company->state;
            $settings->zip_code = $user->company->zip_code;
            $settings->logo_path = $user->company->logo_path;
        }

        $userNiche = $user->niche ?? 'workshop';
        return view('content.settings.company-data.index', compact('settings', 'userNiche'));
    }

    public function update(Request $request)
    {
        $settings = CompanySetting::first() ?? new CompanySetting();
        $user = auth()->user();

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
            'niche' => 'nullable|string|in:workshop,automotive,electronics,pet,beauty_clinic,construction',
            'remove_logo' => 'nullable|boolean'
        ]);

        // Handle Logo Removal
        if ($request->remove_logo) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
                $settings->logo_path = null;
            }
            if ($user->company && $user->company->logo_path) {
                Storage::disk('public')->delete($user->company->logo_path);
                $user->company->update(['logo_path' => null]);
            }
        }

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $path = $request->file('logo')->store('company', 'public');
            $settings->logo_path = $path;

            if ($user->company) {
                $user->company->update(['logo_path' => $path]);
            }
        }

        $settings->fill($validated);
        $settings->save();

        // Update User and Company Niche
        if ($request->has('niche')) {
            $user->niche = $request->niche;
            $user->save();

            if ($user->company) {
                $user->company->niche = $request->niche;
                $user->company->save();
            }
        }

        return response()->json(['success' => true, 'message' => 'Dados atualizados com sucesso!']);
    }
}
