<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class StalePasswordsAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Collection $accesses)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Contraseñas con rotación pendiente · '.$this->accesses->count().' accesos')
            ->view('emails.accesses.stale_passwords', [
                'accesses' => $this->accesses,
                'recipient' => $notifiable,
                'url' => route('accesses.index', ['stale' => 1]),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'access.stale_passwords',
            'title' => 'Contraseñas pendientes de rotar',
            'message' => $this->accesses->count().' contraseñas con más de 90 días',
            'count' => $this->accesses->count(),
            'url' => route('accesses.index', ['stale' => 1]),
            'icon' => 'shield',
        ];
    }
}
