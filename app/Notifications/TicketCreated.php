<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('['.$this->ticket->code.'] Nuevo ticket: '.$this->ticket->subject)
            ->view('emails.tickets.created', [
                'ticket' => $this->ticket,
                'recipient' => $notifiable,
                'url' => route('tickets.show', $this->ticket),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ticket.created',
            'title' => 'Nuevo ticket creado',
            'message' => $this->ticket->code.' · '.$this->ticket->subject,
            'ticket_id' => $this->ticket->id,
            'ticket_code' => $this->ticket->code,
            'priority' => $this->ticket->priority,
            'url' => route('tickets.show', $this->ticket),
            'icon' => 'ticket',
        ];
    }
}
