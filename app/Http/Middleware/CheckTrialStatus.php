<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTrialStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Se o usuário não tem plano definido ou o plano ainda é 'free'
        if ($user && ($user->plan === 'free' || empty($user->plan))) {
            
            // Verifica se a data de término do teste já passou
            if ($user->trial_ends_at && now()->greaterThan($user->trial_ends_at)) {
                $currentRouteName = $request->route()->getName();

                $allowedRoutes = [
                    'settings',
                    'logout',
                    'sanctum.csrf-cookie',
                    'dashboard',
                    'settings.select-plan',
                    'settings.generate-payment'
                ];

                // Permite acesso apenas às rotas de faturamento e perfil
                if (
                    in_array($currentRouteName, $allowedRoutes) ||
                    str_starts_with($currentRouteName ?? '', 'settings.') ||
                    str_starts_with($currentRouteName ?? '', 'profile.') ||
                    str_starts_with($currentRouteName ?? '', 'livewire.')
                ) {
                    return $next($request);
                }

                // Bloqueia acesso para o restante do sistema
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Seu período de teste expirou. Atualize seu plano.'], 403);
                }

                return redirect()->route('settings')->with('error', 'Seu período de teste expirou. Por favor, assine um plano para continuar.');
            }
        }

        return $next($request);
    }
}
