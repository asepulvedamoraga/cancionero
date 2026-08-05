<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    public function __construct()
    {
        $this->locale('es');
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifica tu correo electrónico')
            ->greeting('¡Hola, '.$notifiable->name.'!')
            ->line('Gracias por crear tu cuenta en '.config('app.name').'.')
            ->line('Haz clic en el botón para verificar tu correo electrónico y activar el acceso a tu cuenta.')
            ->action('Verificar mi correo', $this->verificationUrl($notifiable))
            ->line('Si no creaste esta cuenta, puedes ignorar este mensaje.')
            ->salutation('Saludos, el equipo de '.config('app.name'));
    }
}