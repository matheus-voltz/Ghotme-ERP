<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SolutionController extends Controller
{
    public function show($slug)
    {
        $niches = config('niche.niches');
        
        if (!isset($niches[$slug])) {
            abort(404);
        }

        $nicheData = $niches[$slug];
        $apiKey = env('GEMINI_API_KEY');

        // Cache de 24 horas para não gastar API da IA à toa
        $marketingData = Cache::remember("niche_marketing_{$slug}", 86400, function () use ($slug, $nicheData, $apiKey) {
            if (!$apiKey) return $this->getFallbackMarketing($slug);

            $prompt = "Aja como um redator de landing pages de alta conversão.
            Estou criando uma página para o sistema Ghotme, focada no nicho: {$slug}.
            Recursos do sistema para este nicho: " . json_encode($nicheData['labels']) . "
            
            Gere um JSON com:
            1. 'hero_title': Um título impactante que cite o nicho.
            2. 'hero_subtitle': Um subtexto convincente sobre produtividade e lucro.
            3. 'features': Um array de 6 objetos com 'title', 'description' e 'icon' (Ícones REAIS do Tabler Icons, ex: tabler-device-mobile, tabler-chart-pie, tabler-lock, tabler-rocket).
            4. 'pain_point': Um parágrafo profundo sobre a dor real que o dono desse negócio sente e como o Ghotme resolve.
            5. 'cta_text': Uma frase curta para o botão final.
            
            Retorne APENAS o JSON puro, sem markdown.";

            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;
                $response = Http::post($url, [
                    'contents' => [['parts' => [['text' => $prompt]]]]
                ]);

                if ($response->successful()) {
                    $json = $response->json('candidates.0.content.parts.0.text');
                    $json = preg_replace('/^```json|```$/m', '', $json);
                    return json_decode(trim($json), true);
                }
            } catch (\Exception $e) {
                return $this->getFallbackMarketing($slug);
            }

            return $this->getFallbackMarketing($slug);
        });

        return view('content.public.solutions', compact('slug', 'nicheData', 'marketingData'));
    }

    private function getFallbackMarketing($slug)
    {
        return [
            'hero_title' => "O ERP perfeito para o seu negócio",
            'hero_subtitle' => "Gestão inteligente e simplificada para crescer com segurança.",
            'pain_point' => "Chega de planilhas e processos manuais. O Ghotme automatiza sua operação.",
            'features' => [
                ['title' => 'Gestão Completa', 'description' => 'Controle total de clientes e ordens de serviço.', 'icon' => 'tabler-settings'],
                ['title' => 'Financeiro Integrado', 'description' => 'Fluxo de caixa e faturamento em um só lugar.', 'icon' => 'tabler-wallet'],
                ['title' => 'Relatórios IA', 'description' => 'Insights inteligentes para sua tomada de decisão.', 'icon' => 'tabler-robot'],
            ]
        ];
    }
}
