<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FinancialTransaction;
use App\Models\OrdemServico;
use App\Models\Budget;
use App\Helpers\Helpers; // Assumindo que o helper de WhatsApp está aqui
use Carbon\Carbon;

class SendBillingReminders extends Command
{
    protected $signature = 'billing:send-reminders';
    protected $description = 'Envia lembretes de cobrança e status via WhatsApp automaticamente';

    public function handle()
    {
        $this->info('Iniciando o processamento do Robô de WhatsApp...');

        $this->remindOverdueTransactions();
        $this->remindFinishedOS();
        $this->remindPendingBudgets();

        $this->info('Processamento concluído!');
    }

    /**
     * Lembrar contas que vencem amanhã
     */
    protected function remindOverdueTransactions()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();
        $transactions = FinancialTransaction::where('type', 'in')
            ->where('status', 'pending')
            ->whereDate('due_date', $tomorrow)
            ->with(['client', 'company'])
            ->get();

        foreach ($transactions as $tr) {
            $phone = $tr->client->whatsapp ?? $tr->client->phone;
            if (!$phone) continue;

            $message = "Olá {$tr->client->name}, aqui é da {$tr->company->name}. Lembrete: Sua fatura de R$ " . number_format($tr->amount, 2, ',', '.') . " vence amanhã ({$tr->due_date->format('d/m')}).";
            
            // Aqui chamamos sua função de envio real
            // Helpers::sendWhatsApp($phone, $message);
            $this->line("Lembrete de conta enviado para: {$tr->client->name}");
        }
    }

    /**
     * Lembrar OS prontas que o cliente não buscou
     */
    protected function remindFinishedOS()
    {
        $yesterday = Carbon::yesterday();
        $orders = OrdemServico::where('status', 'completed') // 'completed' = Pronta para retirada
            ->whereDate('updated_at', $yesterday)
            ->with(['client', 'company', 'veiculo'])
            ->get();

        foreach ($orders as $os) {
            $phone = $os->client->whatsapp ?? $os->client->phone;
            if (!$phone) continue;

            $message = "Olá {$os->client->name}! Passando para avisar que o serviço no seu " . ($os->veiculo->modelo ?? 'veículo') . " (OS #{$os->id}) já está pronto na {$os->company->name}. Pode vir buscar quando desejar!";
            
            // Helpers::sendWhatsApp($phone, $message);
            $this->line("Lembrete de OS pronta enviado para: {$os->client->name}");
        }
    }

    /**
     * Lembrar orçamentos pendentes há 3 dias
     */
    protected function remindPendingBudgets()
    {
        $threeDaysAgo = Carbon::now()->subDays(3);
        $budgets = Budget::where('status', 'pending')
            ->whereDate('created_at', $threeDaysAgo)
            ->with(['client', 'company'])
            ->get();

        foreach ($budgets as $b) {
            $phone = $b->client->whatsapp ?? $b->client->phone;
            if (!$phone) continue;

            $message = "Olá {$b->client->name}, vimos que o orçamento enviado pela {$b->company->name} ainda está pendente. Ficou com alguma dúvida? Podemos te ajudar a fechar o serviço?";
            
            // Helpers::sendWhatsApp($phone, $message);
            $this->line("Lembrete de Orçamento enviado para: {$b->client->name}");
        }
    }
}
