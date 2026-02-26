<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\OrdemServico;
use App\Models\OrdemServicoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesHubController extends Controller
{
    public function index()
    {
        // 1. Novos Clientes (últimos 7 dias)
        $newClients = Clients::where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Clientes para o Radar de IA (clientes sem OS/Orçamento há mais de 60 dias)
        // Simulando lógica de churn/re-engajamento
        $staleClients = Clients::whereDoesntHave('fieldValues', function($q) {
            // Lógica simplificada: clientes antigos sem atividade recente
        })->limit(5)->get();

        // 3. Conversas Ativas
        $activeChats = ChatMessage::select('client_id', DB::raw('count(*) as msg_count'))
            ->groupBy('client_id')
            ->with('client')
            ->orderBy('msg_count', 'desc')
            ->limit(10)
            ->get();

        // 4. Follow-up do Paciente (Pós-Procedimento)
        // Busca OS finalizadas que possuem serviços com dias de acompanhamento definidos
        $followUpAlerts = OrdemServicoItem::whereHas('service', function($q) {
                $q->where('follow_up_days', '>', 0);
            })
            ->whereHas('ordemServico', function($q) {
                $q->whereIn('status', ['completed', 'finalized']);
            })
            ->with(['service', 'ordemServico.client'])
            ->get()
            ->filter(function($item) {
                $dueDate = $item->ordemServico->updated_at->addDays($item->service->follow_up_days);
                // Alertar se a data de acompanhamento é hoje ou nos últimos 3 dias (janela de oportunidade)
                return $dueDate->isToday() || ($dueDate->isPast() && $dueDate->diffInDays(now()) <= 3);
            });

        return view('content.pages.sales-hub.index', compact('newClients', 'staleClients', 'activeChats', 'followUpAlerts'));
    }

    public function getAiInsight(Request $request)
    {
        $clientId = $request->client_id;
        $client = Clients::findOrFail($clientId);
        
        $niche = niche('name'); // Pega o nome do nicho atual do sistema

        $prompt = "Aja como um consultor de vendas direto e persuasivo especialista no nicho {$niche}.
        O cliente se chama {$client->name}.
        Gere APENAS uma mensagem curta e matadora para o WhatsApp para reconquistá-lo após um tempo de inatividade.
        REGRAS CRÍTICAS:
        1. Retorne APENAS o texto da mensagem, nada mais.
        2. Não dê explicações, não diga 'Entendido', não use introduções.
        3. Use emojis de forma moderada.
        4. Se precisar sugerir um desconto ou serviço, coloque entre colchetes, ex: [NOME DO SERVIÇO].
        5. O tom deve ser amigável mas profissional.";

        return $this->callGemini($prompt);
    }

    public function getFollowUpAiInsight(Request $request)
    {
        $itemId = $request->item_id;
        $item = OrdemServicoItem::with(['service', 'ordemServico.client'])->findOrFail($itemId);
        
        $niche = niche('name');
        $clientName = $item->ordemServico->client->name;
        $serviceName = $item->service->name;

        $prompt = "Aja como um especialista em pós-procedimento de uma clínica de estética ({$niche}).
        O(a) paciente {$clientName} realizou o procedimento: {$serviceName}.
        Gere APENAS uma mensagem de WhatsApp para perguntar como está a recuperação dele(a) e se ele(a) está gostando dos resultados.
        REGRAS CRÍTICAS:
        1. Retorne APENAS o texto da mensagem, nada mais.
        2. Seja carinhoso(a) e atencioso(a), mas sem enrolação.
        3. Use emojis adequados ao contexto de cuidado e beleza.
        4. No final, sugira que se houver dúvidas, ele(a) pode entrar em contato.";

        return $this->callGemini($prompt);
    }

    private function callGemini($prompt)
    {
        $apiKey = env('GEMINI_API_KEY');
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;
            $response = \Illuminate\Support\Facades\Http::post($url, [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            if ($response->successful()) {
                $content = $response->json('candidates.0.content.parts.0.text');
                $content = preg_replace('/^```html|```$/m', '', $content);
                return response()->json(['success' => true, 'insight' => trim($content)]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['success' => false, 'message' => 'Erro ao gerar conteúdo pela IA']);
    }
}
