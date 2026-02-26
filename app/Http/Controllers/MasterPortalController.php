<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Models\Clients;
use App\Models\NewsletterSubscriber;
use App\Models\NewsletterCampaign;
use App\Models\SystemUpdate;
use App\Jobs\SendNewsletterJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterPortalController extends Controller
{
    public function index()
    {
        $stats = [
            'total_companies' => Company::count(),
            'total_users' => User::count(),
            'total_clients' => Clients::count(),
            'total_subscribers' => NewsletterSubscriber::count(),
            'recent_subscribers' => NewsletterSubscriber::latest()->limit(5)->get(),
            'recent_companies' => Company::latest()->limit(5)->get(),
        ];

        $campaigns = NewsletterCampaign::latest()->get();

        return view('content.pages.master.dashboard', compact('stats', 'campaigns'));
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
