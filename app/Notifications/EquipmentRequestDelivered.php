<?php

namespace App\Notifications;

use App\Models\EquipmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentRequestDelivered extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public EquipmentRequest $request)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('['.$this->request->code.'] Equipo entregado')
            ->view('emails.equipment_requests.delivered', [
                'request' => $this->request,
                'recipient' => $notifiable,
                'url' => route('equipment_requests.show', $this->request),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'equipment_request.delivered',
            'title' => 'Equipo entregado',
            'message' => $this->request->code.' · '.$this->request->title,
            'equipment_request_id' => $this->request->id,
            'url' => route('equipment_requests.show', $this->request),
            'icon' => 'check',
        ];
    }
}
