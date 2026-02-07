<?php

namespace App\Http\Controllers;

use App\Models\SystemError;
use Illuminate\Http\Request;

class SystemErrorController extends Controller
{
    public function index()
    {
        $errors = SystemError::orderBy('created_at', 'desc')->paginate(20);
        return view('content.settings.system-errors.index', compact('errors'));
    }

    public function show($id)
    {
        $error = SystemError::findOrFail($id);
        return response()->json($error);
    }

    public function destroyAll()
    {
        SystemError::truncate();
        return back()->with('success', 'Logs de erro limpos com sucesso.');
    }
}