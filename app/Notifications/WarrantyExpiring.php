<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class WarrantyExpiring extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Collection $assets)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Garantías próximas a vencer · '.$this->assets->count().' equipos')
            ->view('emails.assets.warranty_expiring', [
                'assets' => $this->assets,
                'recipient' => $notifiable,
                'url' => route('assets.index'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'asset.warranty_expiring',
            'title' => 'Garantías por vencer',
            'message' => $this->assets->count().' activos con garantía próxima a expirar',
            'count' => $this->assets->count(),
            'url' => route('assets.index'),
            'icon' => 'shield',
        ];
    }
}
