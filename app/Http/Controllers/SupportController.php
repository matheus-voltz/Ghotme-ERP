<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function chatWhatsapp()
    {
        // Número de suporte atualizado
        $phone = "5541991391687"; 
        $message = "Olá! Preciso de suporte com o sistema Ghotme.";
        $url = "https://api.whatsapp.com/send?phone=" . $phone . "&text=" . urlencode($message);
        
        return redirect()->away($url);
    }

    public function chat()
    {
        return view('content.apps.app-chat');
    }

    public function knowledgeBase()
    {
        return view('content.pages.support.knowledge-base');
    }

    public function openTicket()
    {
        return view('content.pages.support.open-ticket');
    }

    public function sendTicket(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high',
            'message' => 'required|string',
        ]);

        $user = Auth::user();
        
        try {
            // Envia o e-mail para o suporte
            Mail::to('suporte@ghotme.com.br')->send(new \App\Mail\SupportTicketMail($validated, $user));
            
            return back()->with('success', 'Seu chamado foi enviado com sucesso! Nossa equipe entrará em contato em breve.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao enviar chamado de suporte: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Ocorreu um erro ao enviar seu chamado. Por favor, tente novamente mais tarde ou use o WhatsApp.');
        }
    }
}