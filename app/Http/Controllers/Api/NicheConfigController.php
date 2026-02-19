<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class NicheConfigController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $niche = $user->niche ?? 'automotive';
        
        // Carrega as configurações do arquivo config/niche.php
        $config = Config::get("niche.niches.{$niche}");

        if (!$config) {
            $config = Config::get("niche.niches.automotive");
        }

        return response()->json([
            'niche' => $niche,
            'labels' => $config['labels'],
            'icons' => $config['icons'] ?? [],
        ]);
    }
}
