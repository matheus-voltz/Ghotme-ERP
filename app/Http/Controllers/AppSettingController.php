<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;

use Illuminate\Support\Facades\Auth;

class AppSettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::where('company_id', Auth::user()->company_id)->first() ?? new AppSetting();
        return view('content.settings.os-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = AppSetting::where('company_id', Auth::user()->company_id)->first();
        
        if (!$settings) {
            $settings = new AppSetting();
            $settings->company_id = Auth::user()->company_id;
        }

        $settings->fill($request->all());
        $settings->save();

        return response()->json(['success' => true, 'message' => 'Configurações de OS atualizadas!']);
    }
}