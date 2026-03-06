<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiConsultantChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ApiAiConsultantController extends Controller
{
    /**
     * Lista todas as conversas do usuário.
     */
    public function index()
    {
        $user = Auth::user();
        $chats = AiConsultantChat::where('user_id', $user->id)
            ->where('company_id', $user->company_id)
            ->orderBy('updated_at', 'desc')
            ->get(['id', 'title', 'created_at', 'updated_at']);

        return response()->json($chats);
    }

    /**
     * Cria uma nova conversa.
     */
    public function store()
    {
        $user = Auth::user();
        $chat = AiConsultantChat::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'title' => 'Nova Consultoria ' . now()->format('d/m H:i')
        ]);

        return response()->json($chat, 201);
    }

    /**
     * Envia uma mensagem e dispara o Job de processamento.
     */
    public function send(Request $request, $id)
    {
        $user = Auth::user();
        $chat = AiConsultantChat::where('user_id', $user->id)->findOrFail($id);
        $company = $user->company;

        // Verificação de plano
        if (!$user->hasFeature('ai_analysis')) {
            return response()->json(['error' => 'Plano insuficiente para usar a IA.'], 403);
        }

        // Limite mensal (Plano Padrão = 10 consultas)
        if (!$user->hasFeature('ai_unlimited')) {
            $monthKey = now()->format('Y-m');
            $usageKey = "ai_usage_{$user->company_id}_{$monthKey}";
            $usageCount = Cache::get($usageKey, 0);

            if ($usageCount >= 10) {
                return response()->json([
                    'error' => 'Limite mensal de 10 consultas atingido no plano Padrão.'
                ], 403);
            }
            Cache::put($usageKey, $usageCount + 1, now()->addMonth());
        }

        $userMessage = $request->input('message');

        // Salva mensagem do usuário
        $chat->messages()->create([
            'role' => 'user',
            'content' => $userMessage
        ]);

        // Dispara o processamento em background
        \App\Jobs\ProcessAiConsultantMessage::dispatch($chat->id, $userMessage, $user, $company);

        return response()->json([
            'success' => true,
            'message' => 'Sua mensagem está sendo processada.',
            'pending' => true
        ]);
    }

    /**
     * Retorna as mensagens de uma conversa (usado para polling).
     */
    public function messages($id)
    {
        $user = Auth::user();
        $chat = AiConsultantChat::where('user_id', $user->id)->findOrFail($id);

        $messages = $chat->messages()->orderBy('created_at', 'asc')->get()->map(function ($msg) {
            return [
                'id' => $msg->id,
                'role' => $msg->role,
                'content' => $msg->content,
                'created_at' => $msg->created_at->toISOString(),
            ];
        });

        return response()->json($messages);
    }
}
