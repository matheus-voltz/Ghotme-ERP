<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;



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

        // Gera um código de 6 dígitos
        $otp = (string) rand(100000, 999999);

        // Atualiza o token no banco de dados com o hash do código de 6 dígitos
        // Isso permite que o Laravel valide o código como se fosse o token original
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now()
            ]
        );

        $url = url(route('password.reset', [
            'token' => $otp,
            'email' => $email,
        ], false));

        return (new MailMessage)
            ->subject('🔐 Redefinição de senha - Ghotme')
            ->view('emails.reset-password', [
                'url' => $url,
                'otp' => $otp,
            ]);
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
