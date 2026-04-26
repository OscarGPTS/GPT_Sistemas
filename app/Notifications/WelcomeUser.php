<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeUser extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ?string $tempPassword = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenido a GPT Services · Inventario TI')
            ->view('emails.users.welcome', [
                'user' => $notifiable,
                'tempPassword' => $this->tempPassword,
                'url' => route('login'),
            ]);
    }
}
