<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;

class AppSettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::first() ?? new AppSetting();
        return view('content.settings.os-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = AppSetting::first() ?? new AppSetting();
        $settings->fill($request->all());
        $settings->save();

        return response()->json(['success' => true, 'message' => 'Configurações de OS atualizadas!']);
    }
}