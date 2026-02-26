<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class MasterAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_master) {
            return $next($request);
        }

        abort(403, 'Acesso restrito ao proprietário do sistema.');
    }
}
