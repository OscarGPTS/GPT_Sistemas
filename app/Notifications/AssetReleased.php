<?php

namespace App\Notifications;

use App\Models\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssetReleased extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Asset $asset, public ?string $reason = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Se liberó un activo a tu cargo: '.$this->asset->internal_code)
            ->view('emails.assets.released', [
                'asset' => $this->asset,
                'reason' => $this->reason,
                'recipient' => $notifiable,
                'url' => route('assets.show', $this->asset),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'asset.released',
            'title' => 'Activo liberado',
            'message' => 'El activo '.$this->asset->internal_code.' ya no está a tu cargo.',
            'asset_id' => $this->asset->id,
            'asset_code' => $this->asset->internal_code,
            'url' => route('assets.show', $this->asset),
            'icon' => 'asset',
        ];
    }
}
