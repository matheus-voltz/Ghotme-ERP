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

        // Busca contexto do negócio
        $nicheKey = get_current_niche();
        $nichesNames = [
            'workshop' => 'Oficina Mecânica',
            'automotive' => 'Centro Automotivo',
            'electronics' => 'Assistência Técnica de Eletrônicos',
            'pet' => 'Pet Shop e Clínica Veterinária',
            'beauty_clinic' => 'Clínica de Estética',
            'construction' => 'Construtora e Empreiteira'
        ];
        $nicheName = $nichesNames[$nicheKey] ?? $nicheKey;

        $revenue = FinancialTransaction::where('company_id', $companyId)->where('type', 'in')->where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('amount');
        $expenses = FinancialTransaction::where('company_id', $companyId)->where('type', 'out')->where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('amount');
        $pendingOS = OrdemServico::where('company_id', $companyId)->where('status', 'pending')->count();

        $companyName = $company->name ?? 'sua empresa';

        $systemKnowledge = "
        CONHECIMENTO DO SISTEMA GHOTME ERP:
        - Dashboard: Exibe métricas de Receita, Despesas, Ticket Médio, Conversora e Lucratividade.
        - Ordens de Serviço: Gerenciamento total desde a abertura, execução (timer), checklists com fotos, até a finalização e faturamento.
        - Financeiro: Controle de entradas/saídas, fluxo de caixa mensal e transações detalhadas.
        - CRM/Clientes: Cadastro de clientes e seus ativos (veículos, pets, etc.) com histórico completo.
        - Estoque: Controle de itens, movimentações e alerta de estoque mínimo.
        - Kanban: Fluxo visual para organizar as etapas das Ordens de Serviço.
        - Consultor IA (Você): Capaz de analisar dados financeiros, sugerir melhorias e agora tirar dúvidas sobre como usar o sistema.
        - Customização: O sistema é adaptável via 'Niches' (Oficina, Pet Shop, Estética, etc.) e campos personalizados.
        ";

        $systemPrompt = "Aja como um Consultor Estratégico e Especialista em Suporte do Ghotme ERP para o nicho {$nicheName}. 
        Sua missão é:
        1. Ajudar o dono da '{$companyName}' a lucrar mais e otimizar processos baseando-se nos dados reais.
        2. Tirar dúvidas sobre o funcionamento do sistema Ghotme ERP usando o conhecimento fornecido.

        DADOS ATUAIS (Mês): Receita R$" . number_format($revenue, 2, ',', '.') . ", Despesas R$" . number_format($expenses, 2, ',', '.') . ", OS Pendentes: {$pendingOS}.
        
        {$systemKnowledge}

        Responda de forma direta, prestativa e profissional. Se o usuário perguntar 'Como faço X no sistema?', use o conhecimento acima para orientar.
        Use Markdown para negritos e listas.";

        // Prepara histórico para o Gemini
        $history = $chat->messages()->orderBy('created_at', 'asc')->get()->map(function ($msg) {
            return [
                'role' => $msg->role === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg->content]]
            ];
        })->toArray();

        // Adiciona o system prompt como instrução inicial se for o primeiro contato ou via context
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

        try {
            $response = Http::post($url, [
                'contents' => array_merge([
                    ['role' => 'user', 'parts' => [['text' => "Instrução do Sistema: " . $systemPrompt]]]
                ], $history)
            ]);

            if ($response->successful()) {
                $aiText = $response->json('candidates.0.content.parts.0.text');

                // Salva mensagem da IA
                $chat->messages()->create([
                    'role' => 'assistant',
                    'content' => trim($aiText)
                ]);

                // Atualiza timestamp do chat para ordenação
                $chat->touch();

                return response()->json([
                    'success' => true,
                    'message' => $aiText
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro na IA: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => false, 'message' => 'Erro ao processar consulta.'], 500);
    }
}
