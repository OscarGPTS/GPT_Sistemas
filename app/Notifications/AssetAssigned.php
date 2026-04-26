<?php

namespace App\Notifications;

use App\Models\Asset;
use App\Models\AssetAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssetAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Asset $asset, public AssetAssignment $assignment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Se te asignó un activo: '.$this->asset->internal_code)
            ->view('emails.assets.assigned', [
                'asset' => $this->asset,
                'assignment' => $this->assignment,
                'recipient' => $notifiable,
                'url' => route('assets.show', $this->asset),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'asset.assigned',
            'title' => 'Nuevo activo asignado',
            'message' => $this->asset->internal_code.' · '.$this->asset->brand.' '.$this->asset->model,
            'asset_id' => $this->asset->id,
            'asset_code' => $this->asset->internal_code,
            'url' => route('assets.show', $this->asset),
            'icon' => 'asset',
        ];
    }
}
