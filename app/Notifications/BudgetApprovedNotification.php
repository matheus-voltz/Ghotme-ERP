<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetApprovedNotification extends Notification
{
    use Queueable;

    protected $budget;

    /**
     * Create a new notification instance.
     */
    public function __construct($budget)
    {
        $this->budget = $budget;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'budget_id' => $this->budget->id,
            'client_name' => $this->budget->client->name ?? 'Cliente',
            'veiculo' => $this->budget->veiculo->modelo ?? 'Veículo',
            'total' => $this->budget->total,
            'title' => 'Orçamento Aprovado',
            'message' => 'O orçamento #' . $this->budget->id . ' foi aprovado pelo cliente.',
            'url' => route('budgets.approved')
        ];
    }
}
