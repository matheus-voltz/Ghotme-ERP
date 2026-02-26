<?php

namespace App\Http\Controllers;

use App\Models\SystemUpdate;
use Illuminate\Http\Request;

class SystemUpdateController extends Controller
{
    public function index()
    {
        $updates = SystemUpdate::orderBy('created_at', 'desc')->paginate(10);
        return view('content.pages.system-updates.index', compact('updates'));
    }
}
