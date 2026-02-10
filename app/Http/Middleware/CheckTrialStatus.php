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

        if ($user && $user->plan === 'free') {
            $trialDuration = 30;
            $createdAt = $user->created_at ?? now();
            $daysUsed = (int)abs(now()->diffInDays($createdAt));
            $trialDaysLeft = max(0, $trialDuration - $daysUsed);

            // If trial expired
            if ($trialDaysLeft <= 0) {
                $currentRouteName = $request->route()->getName();

                $allowedRoutes = [
                    'settings',
                    'logout',
                    'sanctum.csrf-cookie',
                    'dashboard' // Maybe allow dashboard to redirect? No, redirect to settings.
                ];

                // Allow if route name is exactly in allowed list
                // OR if it starts with 'settings.' (billing actions)
                // OR if it starts with 'profile.' (user profile actions)
                // OR if it starts with 'livewire.' (needed for components to work)
                if (
                    in_array($currentRouteName, $allowedRoutes) ||
                    str_starts_with($currentRouteName ?? '', 'settings.') ||
                    str_starts_with($currentRouteName ?? '', 'profile.') ||
                    str_starts_with($currentRouteName ?? '', 'livewire.')
                ) {
                    return $next($request);
                }

                // Block access
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Seu período de teste expirou. Atualize seu plano.'], 403);
                }

                return redirect()->route('settings')->with('error', 'Seu período de teste expirou. Por favor, assine um plano para continuar.');
            }
        }

        return $next($request);
    }
}
