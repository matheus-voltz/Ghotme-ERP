<?php

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class TelegramHandler extends AbstractProcessingHandler
{
    public function __construct(int|string|Level $level = Level::Error)
    {
        parent::__construct($level);
    }

    protected function write(LogRecord $record): void
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (! $token || ! $chatId) {
            return;
        }

        $emoji = match (true) {
            $record->level->value >= Level::Critical->value => '🔴',
            default => '❌',
        };

        $message = mb_strimwidth($record->message, 0, 800, '...');

        $text = "{$emoji} *Ghotme ERP — {$record->level->getName()}*\n\n"
            . "```{$message}```\n\n"
            . '_' . now()->format('d/m/Y H:i:s') . '_';

        $ch = curl_init("https://api.telegram.org/bot{$token}/sendMessage");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_POSTFIELDS => http_build_query([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]),
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
