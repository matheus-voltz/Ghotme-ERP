<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Models\NewsletterCampaign;
use App\Models\SystemUpdate;
use App\Jobs\SendNewsletterJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NewsletterAdminController extends Controller
{
    public function index()
    {
        $subscribers = NewsletterSubscriber::orderBy('created_at', 'desc')->paginate(20);
        $campaigns = NewsletterCampaign::orderBy('created_at', 'desc')->get();
        return view('content.pages.newsletter.index', compact('subscribers', 'campaigns'));
    }

    public function create()
    {
        return view('content.pages.newsletter.compose');
    }

    public function generateContent(Request $request)
    {
        $request->validate(['prompt' => 'required|string']);

        $apiKey = env('GEMINI_API_KEY');

        // Busca as últimas 5 atualizações do sistema para dar contexto à IA
        $updates = SystemUpdate::latest()->limit(5)->get();
        $updatesContext = "";
        foreach($updates as $up) {
            $updatesContext .= "- " . $up->title . ": " . $up->description . "\n";
        }

        if (!$apiKey) {
            return response()->json([
                'success' => false, 
                'message' => 'Configuração de IA ausente. Verifique a chave GEMINI_API_KEY no seu arquivo .env'
            ], 500);
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

            $promptText = "Aja como um redator de e-mails profissionais para o sistema Ghotme.
            O usuário quer falar sobre: " . $request->prompt . ".
            
            CONTEXTO REAL DO SISTEMA (Novas funcionalidades implementadas):
            " . ($updatesContext ?: "Nenhuma atualização recente registrada.") . "
            
            REGRAS OBRIGATÓRIAS:
            1. Retorne APENAS o corpo do e-mail em HTML (h2, p, strong, ul, li).
            2. Se houver atualizações no contexto, mencione-as de forma empolgante.
            3. NÃO inclua tags de estrutura (html, body, head).
            4. NÃO dê nenhuma explicação após o texto.";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $promptText]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 2048,
                ]
            ]);

            if ($response->successful()) {
                $content = $response->json('candidates.0.content.parts.0.text');
                
                // --- LIMPEZA RIGOROSA ---
                // 1. Remove blocos de código markdown (```html ... ```)
                $content = preg_replace('/```[a-z]*\n?/', '', $content);
                $content = str_replace('```', '', $content);

                // 2. Remove tags de estrutura que quebram o layout
                $content = preg_replace('/<!DOCTYPE.*?>/i', '', $content);
                $content = preg_replace('/<\/?html.*?>/i', '', $content);
                $content = preg_replace('/<\/?head.*?>/i', '', $content);
                $content = preg_replace('/<\/?body.*?>/i', '', $content);
                $content = preg_replace('/<meta.*?>/i', '', $content);
                $content = preg_replace('/<title.*?>.*?<\/title>/i', '', $content);

                // 3. Remove explicações que a IA costuma colocar no fim começando com "**" ou "Explicação"
                $parts = explode('**Explicação', $content);
                $content = $parts[0];
                
                $parts = explode('**Tags HTML', $content);
                $content = $parts[0];

                // 4. Remove atributos de largura/altura que podem quebrar o layout
                $content = preg_replace('/(width|height)="\d+"/', '', $content);
                $content = preg_replace('/style=".*?"/', '', $content);

                return response()->json([
                    'success' => true,
                    'content' => trim($content)
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Erro na resposta do Gemini: ' . $response->body()], 500);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao conectar com o Gemini: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $campaign = NewsletterCampaign::create([
            'subject' => $request->subject,
            'content' => $request->content,
        ]);

        // Dispara o Job de envio em massa
        SendNewsletterJob::dispatch($campaign);

        return redirect()->route('newsletter.admin.index')->with('success', 'Newsletter enviada para a fila de processamento!');
    }

    public function destroySubscriber($id)
    {
        NewsletterSubscriber::findOrFail($id)->delete();
        return back()->with('success', 'Assinante removido com sucesso.');
    }
}
