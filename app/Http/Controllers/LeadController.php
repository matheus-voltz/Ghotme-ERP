<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $lead = \App\Models\Lead::create($validated);

        // Enviar E-mail de Notificação
        try {
            \Illuminate\Support\Facades\Mail::to('grafit933@gmail.com')
                ->send(new \App\Mail\LeadNotification($lead));
        } catch (\Exception $e) {
            // Silencia erro de e-mail para não travar o formulário, mas você pode logar se quiser
            \Illuminate\Support\Facades\Log::error("Erro ao enviar e-mail de lead: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Mensagem enviada com sucesso! Em breve entraremos em contato.'
        ]);
    }
}
