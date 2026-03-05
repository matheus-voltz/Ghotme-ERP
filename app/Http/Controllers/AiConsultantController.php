<?php

namespace App\Http\Controllers;

use App\Models\AiConsultantChat;
use App\Models\AiConsultantMessage;
use App\Models\FinancialTransaction;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AiConsultantController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $chats = AiConsultantChat::where('user_id', $user->id)
            ->where('company_id', $user->company_id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('content.pages.ai-consultant.index', compact('chats'));
    }

    public function createChat()
    {
        $user = Auth::user();
        $chat = AiConsultantChat::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'title' => 'Nova Consultoria ' . now()->format('d/m H:i')
        ]);

        return redirect()->route('ai-consultant.show', $chat->id);
    }

    public function show($id)
    {
        $user = Auth::user();
        $chat = AiConsultantChat::where('user_id', $user->id)->findOrFail($id);
        $messages = $chat->messages;

        $chats = AiConsultantChat::where('user_id', $user->id)
            ->where('company_id', $user->company_id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('content.pages.ai-consultant.chat', compact('chat', 'messages', 'chats'));
    }

    public function sendMessage(Request $request, $id)
    {
        $user = Auth::user();
        $chat = AiConsultantChat::where('user_id', $user->id)->findOrFail($id);
        $companyId = $user->company_id;
        $company = $user->company;

        // Plano e Limites
        if (!$user->hasFeature('ai_analysis')) {
            return response()->json(['success' => false, 'message' => 'Plano insuficiente.'], 403);
        }

        if (!$user->hasFeature('ai_unlimited')) {
            $monthKey = now()->format('Y-m');
            $usageKey = "ai_usage_{$companyId}_{$monthKey}";
            $usageCount = Cache::get($usageKey, 0);

            if ($usageCount >= 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Limite mensal de 10 consultas atingido no plano Padrão.'
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

        // Dispara o processamento da IA em Background (Job)
        \App\Jobs\ProcessAiConsultantMessage::dispatch($chat->id, $userMessage, $user, $company);

        return response()->json([
            'success' => true,
            'message' => 'Sua mensagem está sendo processada. A IA enviará a resposta em instantes.',
            'pending' => true
        ]);
    }

    public function checkMessages($id)
    {
        $user = Auth::user();
        $chat = AiConsultantChat::where('user_id', $user->id)->findOrFail($id);

        $messages = $chat->messages()->orderBy('created_at', 'asc')->get()->map(function ($msg) {
            return [
                'role' => $msg->role,
                'content' => $msg->content
            ];
        });

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }
}
