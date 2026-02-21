<?php

namespace App\Http\Controllers;

use App\Models\SystemError;
use Illuminate\Http\Request;

class SystemErrorController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('dev_authenticated')) {
            return view('content.settings.system-errors.auth');
        }

        $errors = SystemError::orderBy('created_at', 'desc')->paginate(20);
        return view('content.settings.system-errors.index', compact('errors'));
    }

    public function authenticate(Request $request)
    {
        if ($request->password === 'Kvothe1995@.') {
            session(['dev_authenticated' => true]);
            return redirect()->route('settings.system-errors');
        }

        return back()->with('error', 'Senha de desenvolvedor incorreta.');
    }

    public function show($id)
    {
        if (!session()->has('dev_authenticated')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $error = SystemError::findOrFail($id);
        return response()->json($error);
    }

    public function destroyAll()
    {
        if (!session()->has('dev_authenticated')) {
            return back()->with('error', 'Não autorizado.');
        }
        SystemError::truncate();
        return back()->with('success', 'Logs de erro limpos com sucesso.');
    }
}
