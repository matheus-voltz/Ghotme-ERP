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

        // Eventos de atraso
        if ($event === 'PAYMENT_OVERDUE') {
            $this->processOverduePayment($payment);
        }

        return response()->json(['success' => true]);
    }

    private function processPayment($asaasPayment)
    {
        $externalReference = $asaasPayment['externalReference'] ?? null;
        if (!$externalReference) return;

        // Caso 1: Pagamento de Ordem de Serviço (Prefixo 'os_')
        if (str_starts_with($externalReference, 'os_')) {
            $osId = str_replace('os_', '', $externalReference);
            $os = \App\Models\OrdemServico::find($osId);

            if ($os) {
                // Marca a OS como paga e gera a transação financeira
                $os->update(['status' => 'paid']); // Ou o status final de pagamento do seu fluxo

                // Registra no histórico do veículo
                \App\Models\VehicleHistory::create([
                    'company_id' => $os->company_id,
                    'veiculo_id' => $os->veiculo_id,
                    'date' => now(),
                    'event_type' => 'pagamento_recebido',
                    'title' => 'Pagamento PIX Confirmado',
                    'description' => 'Pagamento de R$ ' . number_format($asaasPayment['value'], 2, ',', '.') . ' recebido automaticamente via Asaas.',
                    'performer' => 'Sistema (Asaas Webhook)',
                ]);

                Log::info("OS #{$osId} marked as paid via Webhook.");
            }
            return;
        }

        // Caso 2: Pagamento de Assinatura do Ghotme ERP (User ID direto)
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
            $user->payment_overdue_since = null; // Limpa o atraso, já que pagou
            $user->save();
            Log::info("User {$user->id} plan updated to {$user->plan} via Webhook. Overdue status cleared.");
        } else {
            // Caso não tenha um history pendente, pode ser uma renovação avulsa. Apenas garante que não está marcado como atrasado.
            $user->payment_overdue_since = null;
            $user->save();
        }
    }

    private function processOverduePayment($asaasPayment)
    {
        $externalReference = $asaasPayment['externalReference'] ?? null;
        if (!$externalReference) return;

        // Se for uma OS, não mexe no plano do usuário
        if (str_starts_with($externalReference, 'os_')) {
            return;
        }

        $user = User::find($externalReference);

        if ($user) {
            // Se já não estiver marcado como atrasado, marca com a data de hoje. 
            // Como o Asaas envia o evento no dia que venceu, hoje é o dia 0 do atraso.
            if (is_null($user->payment_overdue_since)) {
                $user->payment_overdue_since = now();
                $user->save();
                Log::warning("Webhook: User {$user->id} marked as PAYMENT_OVERDUE.");
            }
        }
    }
}
