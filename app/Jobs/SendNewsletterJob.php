<?php

namespace App\Jobs;

use App\Mail\NewsletterMail;
use App\Models\NewsletterSubscriber;
use App\Models\NewsletterCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;

    public function __construct(NewsletterCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function handle(): void
    {
        Log::info("Iniciando envio da newsletter: " . $this->campaign->subject);
        
        $subscribers = NewsletterSubscriber::where('is_active', true)->get();
        $count = 0;

        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(new NewsletterMail($this->campaign->subject, $this->campaign->content));
                $count++;
                Log::info("Newsletter enviada para: " . $subscriber->email);
            } catch (\Exception $e) {
                Log::error("Erro ao enviar newsletter para " . $subscriber->email . ": " . $e->getMessage());
            }
        }

        // Atualiza a contagem de envios na campanha
        $this->campaign->update([
            'sent_count' => $count
        ]);

        Log::info("Finalizado envio da newsletter. Total de sucessos: " . $count);
    }
}
