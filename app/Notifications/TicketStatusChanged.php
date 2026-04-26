<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $fromStatus,
        public string $toStatus,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $labels = ['open'=>'Abierto','in_progress'=>'En progreso','on_hold'=>'En espera','resolved'=>'Resuelto','closed'=>'Cerrado','cancelled'=>'Cancelado'];
        return (new MailMessage)
            ->subject('['.$this->ticket->code.'] '.($labels[$this->toStatus] ?? $this->toStatus).': '.$this->ticket->subject)
            ->view('emails.tickets.status_changed', [
                'ticket' => $this->ticket,
                'recipient' => $notifiable,
                'fromLabel' => $labels[$this->fromStatus] ?? $this->fromStatus,
                'toLabel' => $labels[$this->toStatus] ?? $this->toStatus,
                'toStatus' => $this->toStatus,
                'url' => route('tickets.show', $this->ticket),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ticket.status_changed',
            'title' => 'Estado de ticket actualizado',
            'message' => $this->ticket->code.' ahora está '.$this->toStatus,
            'ticket_id' => $this->ticket->id,
            'ticket_code' => $this->ticket->code,
            'from' => $this->fromStatus,
            'to' => $this->toStatus,
            'url' => route('tickets.show', $this->ticket),
            'icon' => 'ticket',
        ];
    }
}
