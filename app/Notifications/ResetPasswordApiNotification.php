<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordApiNotification extends Notification
{
    use Queueable;

    public $token;
    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $resetUrl = config('app.frontend_url', config('app.url')) .
            '/reset-password?' .
            http_build_query([
                'token' => $this->token,
                'email' => $this->email,
            ]);

        return (new MailMessage)
            ->subject('Restablecer Contraseña - ' . config('app.name'))
            ->greeting('¡Hola!')
            ->line('Recibiste este email porque solicitaste restablecer tu contraseña.')
            ->action('Restablecer Contraseña', $resetUrl)
            ->line('Este enlace expirará en ' . config('auth.passwords.users.expire') . ' minutos.')
            ->line('Si no solicitaste este cambio, puedes ignorar este email.')
            ->salutation('Saludos, ' . config('app.name'));
    }
}
