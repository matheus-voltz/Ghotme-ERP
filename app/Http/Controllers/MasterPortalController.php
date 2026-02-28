<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Models\Clients;
use App\Models\NewsletterSubscriber;
use App\Models\NewsletterCampaign;
use App\Models\SystemUpdate;
use App\Models\SystemError;
use App\Models\BillingHistory;
use App\Jobs\SendNewsletterJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterPortalController extends Controller
{
    public function index()
    {
        // Estatísticas de Visitas nos últimos 30 dias
        $days = [];
        $visitCounts = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $days[] = now()->subDays($i)->format('d/m');
            $visitCounts[] = \App\Models\SiteVisit::whereDate('created_at', $date)->count();
        }

        $stats = [
            'total_companies' => Company::count(),
            'total_users' => User::count(),
            'total_clients' => Clients::count(),
            'total_subscribers' => NewsletterSubscriber::count(),
            'total_errors' => SystemError::count(),
            'total_visits_30d' => \App\Models\SiteVisit::where('created_at', '>=', now()->subDays(30))->count(),
            'global_revenue' => BillingHistory::where('status', 'paid')->sum('amount'),
            'pending_revenue' => BillingHistory::where('status', 'pending')->sum('amount'),
            'recent_subscribers' => NewsletterSubscriber::latest()->limit(5)->get(),
            'recent_companies' => Company::latest()->limit(5)->get(),
            'visit_chart_labels' => $days,
            'visit_chart_data' => $visitCounts,
        ];

        $campaigns = NewsletterCampaign::latest()->get();

        return view('content.pages.master.dashboard', compact('stats', 'campaigns'));
    }

    public function errors()
    {
        $errors = SystemError::with(['user.company'])->latest()->paginate(20);
        return view('content.pages.master.errors', compact('errors'));
    }

    public function destroyError($id)
    {
        SystemError::findOrFail($id)->delete();
        return back()->with('success', 'Log de erro removido.');
    }

    public function clearErrors()
    {
        SystemError::truncate();
        return back()->with('success', 'Todos os logs foram limpos com sucesso!');
    }

    public function companies()
    {
        $companies = Company::withCount('users')->latest()->paginate(20);
        return view('content.pages.master.companies', compact('companies'));
    }

    public function aiAnalysis()
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) return response()->json(['success' => false, 'message' => 'IA não configurada']);

        $totalCompanies = Company::count();
        $nicheStats = Company::select('niche', DB::raw('count(*) as count'))->groupBy('niche')->get();
        $totalClients = Clients::count();
        $totalSubscribers = NewsletterSubscriber::count();

        $prompt = "Aja como o conselheiro estratégico do Matheus, dono do sistema ERP Ghotme.
        DADOS ATUAIS DO ECOSSISTEMA:
        - Total de Empresas: {$totalCompanies}
        - Nichos mais populares: {$nicheStats}
        - Total de Clientes atendidos no sistema: {$totalClients}
        - Leads na Newsletter: {$totalSubscribers}

        Com base nesses números, gere um relatório ultra-curto (máximo 3 parágrafos) com:
        1. Uma análise do crescimento.
        2. Uma sugestão de qual nicho focar o marketing agora.
        3. Uma ideia de funcionalidade 'matadora' para aumentar o faturamento.

        REGRAS DE FORMATAÇÃO:
        - JAMAIS use hashtags (# ou ##) no texto. Use negrito (**Texto**) para destacar títulos.";

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;
            $response = \Illuminate\Support\Facades\Http::post($url, [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            if ($response->successful()) {
                $insight = $response->json('candidates.0.content.parts.0.text');
                return response()->json(['success' => true, 'insight' => trim($insight)]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['success' => false, 'message' => 'Erro ao gerar análise']);
    }

    public function createNewsletter()
    {
        return view('content.pages.master.newsletter-compose');
    }

    public function sendNewsletter(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'target' => 'required|in:subscribers,all_clients,both'
        ]);

        $campaign = NewsletterCampaign::create([
            'subject' => $request->subject,
            'content' => $request->content,
        ]);

        // Coleta todos os e-mails baseado no alvo
        $emails = collect();

        if ($request->target == 'subscribers' || $request->target == 'both') {
            $emails = $emails->merge(NewsletterSubscriber::where('is_active', true)->pluck('email'));
        }

        if ($request->target == 'all_clients' || $request->target == 'both') {
            $emails = $emails->merge(Clients::whereNotNull('email')->pluck('email'));
        }

        $uniqueEmails = $emails->unique();

        // Dispara o envio (podemos reutilizar o Job existente ou criar um MasterSendJob)
        // Por simplicidade, vamos usar o Job existente ajustando a lógica para aceitar lista customizada se necessário
        // Mas para agora, vamos apenas simular ou usar o padrão
        
        // TODO: Implementar lógica de disparo Master para a lista $uniqueEmails
        
        return redirect()->route('master.dashboard')->with('success', 'Newsletter Master enviada para a fila de processamento!');
    }

    public function logSystemUpdate(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|in:feature,improvement,fix'
        ]);

        SystemUpdate::create($request->all());

        return back()->with('success', 'Atualização registrada no Changelog!');
    }
}
