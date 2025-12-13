<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;



class ResetPasswordCustom extends BaseResetPassword
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        parent::__construct($token);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $email = method_exists($notifiable, 'getEmailForPasswordReset')
            ? $notifiable->getEmailForPasswordReset()
            : (property_exists($notifiable, 'email') ? $notifiable->email : null);

        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $email,
        ], false));

        return (new MailMessage)
            ->subject('🔐 Redefinição de senha - Ghotme')
            ->greeting('Olá!')
            ->line('Recebemos uma solicitação para redefinir sua senha.')
            ->action('Redefinir senha', $url)
            ->line('Este link expira em 60 minutos.')
            ->salutation('Atenciosamente, equipe Ghotme');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
