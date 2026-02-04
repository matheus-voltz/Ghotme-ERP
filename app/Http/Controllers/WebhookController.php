<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BillingHistory;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Recebe notificações do Asaas sobre pagamentos e assinaturas.
     */
    public function asaas(Request $request)
    {
        // Token de segurança enviado pelo Asaas no cabeçalho
        $asaasToken = env('ASAAS_WEBHOOK_TOKEN');
        if ($request->header('asaas-access-token') !== $asaasToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $event = $request->input('event');
        $payment = $request->input('payment');

        Log::info("Asaas Webhook Event: {$event}", ['payment_id' => $payment['id'] ?? 'N/A']);

        // Eventos de pagamento confirmado
        if (in_array($event, ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED'])) {
            $this->processPayment($payment);
        }

        return response()->json(['success' => true]);
    }

    private function processPayment($asaasPayment)
    {
        $customerId = $asaasPayment['customer'];
        $externalReference = $asaasPayment['externalReference'] ?? null;

        // Tenta achar o usuário pelo externalReference (User ID) ou pelo e-mail
        $user = User::find($externalReference);
        
        if (!$user) {
            Log::warning("Webhook: User not found for ID {$externalReference}. Searching by email.");
            // Fallback para buscar o cliente no Asaas para pegar o e-mail se necessário
            // Por simplicidade, vamos assumir que externalReference é o User ID.
            return;
        }

        // 1. Atualiza histórico local
        $history = BillingHistory::where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($history) {
            $history->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // 2. Atualiza o plano do usuário baseado no nome registrado no histórico
            // Ex: "Padrao (Mensal)" -> Extrai "padrao" e "monthly"
            $planName = strtolower($history->plan_name);
            
            if (str_contains($planName, 'enterprise')) {
                $user->plan = 'enterprise';
            } else {
                $user->plan = 'padrao';
            }

            if (str_contains($planName, 'anual') || str_contains($planName, 'yearly')) {
                $user->plan_type = 'yearly';
                $user->trial_ends_at = now()->addYear();
            } else {
                $user->plan_type = 'monthly';
                $user->trial_ends_at = now()->addMonth();
            }

            $user->selected_plan = null; // Limpa a seleção temporária pois o plano agora é oficial
            $user->save();
            Log::info("User {$user->id} plan updated to {$user->plan} via Webhook.");
        }
    }
}