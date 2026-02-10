<?php

use SergiX44\Nutgram\Nutgram;
use App\Models\User;
use App\Models\ChatMessage;

/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram.
|
*/

$bot->onCommand('start', function (Nutgram $bot) {
    // Quando o usuário manda /start, salvamos o ID dele se ele passar o email
    // Ex: /start suporte@ghotme.com.br
    
    $args = $bot->message()->text;
    $parts = explode(' ', $args);
    
    if (count($parts) < 2) {
        $bot->sendMessage("Olá! Para conectar seu usuário Ghotme, digite: 
/start seu@email.com");
        return;
    }

    $email = trim($parts[1]);
    $user = User::where('email', $email)->first();

    if ($user) {
        $user->update([
            'telegram_chat_id' => $bot->chatId(),
            'telegram_username' => $bot->user()->username
        ]);
        $bot->sendMessage("✅ Conectado com sucesso ao usuário: {$user->name}!");
    } else {
        $bot->sendMessage("❌ E-mail não encontrado no sistema.");
    }
});

// Quando o bot recebe qualquer mensagem de texto
$bot->onMessage(function (Nutgram $bot) {
    $chatId = $bot->chatId();
    $text = $bot->message()->text;

    $user = User::where('telegram_chat_id', (string)$chatId)->first();

    if ($user) {
        // Salva na tabela chat_messages como se o usuário tivesse enviado
        // O receiver_id seria o ADMIN ou SUPORTE (vamos fixar ou deixar null para ser broadcast)
        // Para simplificar, vou mandar para o primeiro usuário Admin que encontrar
        
        $admin = User::where('role', 'admin')->first() ?? User::first(); 

        if ($admin && $admin->id != $user->id) {
            ChatMessage::create([
                'sender_id' => $user->id,
                'receiver_id' => $admin->id,
                'message' => $text,
                'is_read' => false
            ]);
            
            // Opcional: Avisar que foi recebido
            // $bot->sendMessage("Mensagem recebida pelo suporte!");
        }
    } else {
        $bot->sendMessage("Você não está conectado. Digite /start seu@email.com");
    }
});
