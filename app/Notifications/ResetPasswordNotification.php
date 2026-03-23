<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = "http://localhost:5173/reset-password?token={$this->token}&email={$notifiable->email}";

        return (new MailMessage)
            ->subject('Restablecer contraseña - Arcane Systems')
            ->greeting('Hola ' . $notifiable->nombre . ',')
            ->line('Recibimos una solicitud para restablecer tu contraseña.')
            ->action('Restablecer contraseña', $url)
            ->line('Este enlace expira en 60 minutos.')
            ->line('Si no solicitaste esto, ignora este correo.');
    }
}