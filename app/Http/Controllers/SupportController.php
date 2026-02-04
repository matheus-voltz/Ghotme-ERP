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
        
        // Aqui poderíamos enviar um e-mail real ou salvar no banco
        // Por enquanto, vamos simular o sucesso
        
        return back()->with('success', 'Seu chamado foi enviado com sucesso! Retornaremos em breve.');
    }
}