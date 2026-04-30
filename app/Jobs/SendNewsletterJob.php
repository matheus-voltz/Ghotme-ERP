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
    protected array $customEmails;

    public function __construct(NewsletterCampaign $campaign, array $customEmails = [])
    {
        $this->campaign = $campaign;
        $this->customEmails = $customEmails;
    }

    public function handle(): void
    {
        $emails = count($this->customEmails) > 0
            ? collect($this->customEmails)
            : NewsletterSubscriber::where('is_active', true)->pluck('email');

        $count = 0;

        foreach ($emails as $email) {
            try {
                Mail::to($email)->send(new NewsletterMail($this->campaign->subject, $this->campaign->content));
                $count++;
            } catch (\Exception $e) {
                Log::error("Erro ao enviar newsletter para {$email}: " . $e->getMessage());
            }
        }

        $this->campaign->update(['sent_count' => $count]);
    }
}
