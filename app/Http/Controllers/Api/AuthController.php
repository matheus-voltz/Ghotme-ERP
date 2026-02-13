<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Credenciais inválidas'], 401);
        }

        // VERIFICAÇÃO DE 2FA (Padrão Jetstream/Fortify)
        if ($user->two_factor_secret && $user->two_factor_confirmed_at) {
            return response()->json([
                'two_factor' => true,
                'message' => 'Autenticação de dois fatores necessária.',
                'email' => $request->email // Devolvemos o email para o próximo passo
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user
        ]);
    }

    public function loginTwoFactor(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        // Valida o código de 6 dígitos usando a lógica nativa do Fortify
        if (! $user->two_factor_secret || 
            ! decrypt($user->two_factor_secret) || 
            ! app(\Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider::class)->verify(
                decrypt($user->two_factor_secret), $request->code
            )) {
            return response()->json(['message' => 'Código de autenticação inválido.'], 422);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logout realizado com sucesso']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
