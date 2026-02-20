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

        $email = trim($request->email);
        $user = User::where('email', $email)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            \Illuminate\Support\Facades\Log::warning('Login falhou para: ' . $request->email);
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
        $user->is_expired = $user->isTrialExpired();

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
        if (
            ! $user->two_factor_secret ||
            ! decrypt($user->two_factor_secret) ||
            ! app(\Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider::class)->verify(
                decrypt($user->two_factor_secret),
                $request->code
            )
        ) {
            return response()->json(['message' => 'Código de autenticação inválido.'], 422);
        }

        $token = $user->createToken('auth-token')->plainTextToken;
        $user->is_expired = $user->isTrialExpired();

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
        $user = $request->user();
        $user->is_expired = $user->isTrialExpired();
        return response()->json($user);
    }

    public function updateProfilePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:10240', // 10MB Max
        ]);

        $user = $request->user();

        if ($request->hasFile('photo')) {
            $user->updateProfilePhoto($request->file('photo'));

            // Força a atualização da URL para retorno imediato
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Foto de perfil atualizada!',
                'profile_photo_url' => $user->profile_photo_url,
                'user' => $user
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Nenhuma imagem enviada.'], 400);
    }

    public function updatePushToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $request->user()->update([
            'expo_push_token' => $request->token
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token de notificação atualizado com sucesso.'
        ]);
    }
}
