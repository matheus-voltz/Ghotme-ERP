<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\Helpers;
use App\Models\User;

class TestPushNotification extends Command
{
    /**
     * O nome e a assinatura do comando.
     * Exemplo: php artisan ghotme:test-push 10 "Olá Mundo"
     */
    protected $signature = 'ghotme:test-push {user_id} {message=Teste de Notificação Ghotme}';

    /**
     * A descrição do comando.
     */
    protected $description = 'Envia uma notificação push de teste para um usuário específico através do Expo.';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $messageBody = $this->argument('message');

        $user = User::find($userId);

        if (!$user) {
            $this->error("Usuário ID {$userId} não encontrado.");
            return;
        }

        if (!$user->expo_push_token) {
            $this->error("O usuário {$user->name} não possui um token de notificação (expo_push_token) no banco de dados.");
            $this->info("Dica: Logue no app mobile com este usuário para registrar o token.");
            return;
        }

        $this->info("🚀 Enviando notificação para {$user->name}...");
        $this->info("Token: {$user->expo_push_token}");

        $response = Helpers::sendExpoNotification(
            $user->expo_push_token,
            "Teste Ghotme ERP 🚀",
            $messageBody,
            ['type' => 'test', 'sent_at' => now()->toDateTimeString()]
        );

        $this->line("Resposta do Expo:");
        print_r($response);

        if (isset($response['data']['status']) && $response['data']['status'] === 'ok') {
            $this->success("✅ Notificação enviada com sucesso!");
        } else {
            $this->warn("⚠️ O servidor do Expo recebeu o pedido, mas pode haver problemas no aparelho.");
        }
    }
}
