<?php

namespace App\Notifications;

use App\Models\MaintenanceRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceDue extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MaintenanceRecord $record, public bool $overdue = false)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $prefix = $this->overdue ? '[VENCIDO]' : '[Próximo]';
        return (new MailMessage)
            ->subject($prefix.' Mantenimiento: '.$this->record->title)
            ->view('emails.maintenance.due', [
                'record' => $this->record,
                'overdue' => $this->overdue,
                'recipient' => $notifiable,
                'url' => route('maintenance.index'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->overdue ? 'maintenance.overdue' : 'maintenance.due',
            'title' => $this->overdue ? 'Mantenimiento vencido' : 'Mantenimiento próximo',
            'message' => $this->record->asset?->internal_code.' · '.$this->record->title,
            'record_id' => $this->record->id,
            'asset_id' => $this->record->asset_id,
            'url' => route('maintenance.index'),
            'icon' => 'wrench',
        ];
    }
}
