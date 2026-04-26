<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCommented extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket, public TicketComment $comment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('['.$this->ticket->code.'] Nuevo comentario en tu ticket')
            ->view('emails.tickets.commented', [
                'ticket' => $this->ticket,
                'comment' => $this->comment,
                'recipient' => $notifiable,
                'url' => route('tickets.show', $this->ticket),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ticket.commented',
            'title' => 'Nuevo comentario en ticket',
            'message' => $this->ticket->code.' · '.$this->comment->user?->name.' comentó',
            'ticket_id' => $this->ticket->id,
            'ticket_code' => $this->ticket->code,
            'comment_id' => $this->comment->id,
            'url' => route('tickets.show', $this->ticket),
            'icon' => 'comment',
        ];
    }
}
