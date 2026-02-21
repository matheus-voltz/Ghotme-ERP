<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test {email}';
    protected $description = 'Send a test email to verify branding and logo';

    public function handle()
    {
        $email = $this->argument('email');
        $this->info("Sending test email to: {$email}");

        try {
            Mail::send([], [], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Teste de Logo - Ghotme ERP')
                    // Usando a View de notificação padrão para testar o header.blade.php que editamos
                    ->html('<h1>Teste de Marca</h1><p>Se você está vendo este e-mail, o sistema de disparos está funcionando. Verifique se o logo do <strong>Ghotme</strong> aparece corretamente no topo.</p>');
            });

            $this->info('Email sent successfully! Check your inbox (and spam).');
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
            Log::error('Test Mail Error: ' . $e->getMessage());
        }
    }
}
