<?php

namespace App\Jobs;

use App\Helpers\Helpers;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $token;
    protected $title;
    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct(string $token, string $title, string $message)
    {
        $this->token = $token;
        $this->title = $title;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Helpers::sendExpoNotification($this->token, $this->title, $this->message);
    }
}