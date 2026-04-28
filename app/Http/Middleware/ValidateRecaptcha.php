<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class ValidateRecaptcha
{
    public function handle(Request $request, Closure $next): Response
    {
        // Só valida no POST /login
        if (!($request->isMethod('POST') && $request->is('login'))) {
            return $next($request);
        }

        $secret = config('services.recaptcha.secret');

        if (!$secret) {
            return $next($request);
        }

        $token = $request->input('g-recaptcha-response');

        if (!$token) {
            return back()->withErrors(['email' => 'Por favor, confirme que você não é um robô.'])->withInput();
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => $secret,
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        if (!($response->json('success') === true)) {
            return back()->withErrors(['email' => 'Verificação reCAPTCHA falhou. Tente novamente.'])->withInput();
        }

        return $next($request);
    }
}
