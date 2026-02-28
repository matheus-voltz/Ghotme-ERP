<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CheckSupportEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'support:check-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica novos emails de suporte e envia para o Telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $telegramToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (!$telegramToken || !$chatId) {
            $this->error('Telegram Token ou Chat ID não configurados no .env!');
            return;
        }

        try {
            /** @var \Webklex\PHPIMAP\Client $client */
            $client = Client::account('default');
            $client->connect();

            // Pega a caixa de entrada (INBOX)
            $folder = $client->getFolder('INBOX');

            // Busca os emails não lidos (UNSEEN)
            $messages = $folder->query()->unseen()->get();

            if ($messages->count() === 0) {
                $this->info('Nenhum novo email encontrado.');
                return;
            }

            foreach ($messages as $message) {
                $subject = $message->getSubject()[0] ?? 'Sem Assunto';
                $from = $message->getFrom()[0]->mail ?? 'Desconhecido';
                $textBody = $message->getTextBody() ?? $message->getHTMLBody() ?? 'Corpo de email vazio.';

                // 1. Localizar o usuário pelo email
                $sender = \App\Models\User::where('email', $from)->first();
                $masterId = 7; // ID do usuário Master configurado

                if ($sender) {
                    \App\Models\ChatMessage::create([
                        'company_id' => $sender->company_id,
                        'sender_id' => $sender->id,
                        'receiver_id' => $masterId,
                        'message' => "[SUPORTE VIA EMAIL] Assunto: {$subject}\n\n" . strip_tags($textBody),
                        'is_read' => false
                    ]);
                } else {
                    // Se o usuário não existe, poderíamos criar um lead ou apenas logar
                    \Illuminate\Support\Facades\Log::info("Email de suporte recebido de remetente desconhecido: {$from}");
                }

                // Envia para o Telegram (Mantendo a funcionalidade atual)
                $response = Http::post("https://api.telegram.org/bot{$telegramToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $telegramMessage,
                    'parse_mode' => 'Markdown'
                ]);

                if ($response->successful()) {
                    // Marca o email como lido apenas se enviou com sucesso pro Telegram
                    $message->setFlag(['\Seen']);
                    $this->info("Email de {$from} encaminhado pro Telegram e marcado como lido!");
                } else {
                    $this->error("Erro ao enviar pro Telegram: " . $response->body());
                }
            }

            $client->disconnect();
        } catch (\Exception $e) {
            $this->error("Erro ao conectar no IMAP: " . $e->getMessage());
        }
    }
}
