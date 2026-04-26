<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketCommented;
use App\Notifications\TicketCreated;
use App\Notifications\TicketStatusChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TicketService
{
    public function notifyCreated(Ticket $ticket): void
    {
        // Notify IT team
        $itRole = Role::whereIn('name', ['it', 'admin'])->pluck('id');
        $recipients = User::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('roles.id', $itRole))
            ->get();
        Notification::send($recipients, new TicketCreated($ticket));
    }

    public function changeStatus(Ticket $ticket, string $status, ?string $note = null): void
    {
        DB::transaction(function () use ($ticket, $status, $note) {
            $from = $ticket->status;
            $fields = ['status' => $status];
            if ($status === 'resolved' && ! $ticket->resolved_at) {
                $fields['resolved_at'] = now();
            }
            if ($status === 'closed' && ! $ticket->closed_at) {
                $fields['closed_at'] = now();
            }
            $ticket->update($fields);
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'action' => 'status_changed',
                'data' => ['from' => $from, 'to' => $status, 'note' => $note],
            ]);

            if ($from !== $status && $ticket->requester) {
                $ticket->requester->notify(new TicketStatusChanged($ticket, $from, $status));
            }
        });
    }

    public function assign(Ticket $ticket, User $user): void
    {
        DB::transaction(function () use ($ticket, $user) {
            $from = $ticket->assignee_id;
            $ticket->update([
                'assignee_id' => $user->id,
                'status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
            ]);
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'action' => 'assigned',
                'data' => ['from' => $from, 'to' => $user->id],
            ]);

            if ($user->id !== $from) {
                $user->notify(new TicketAssigned($ticket));
            }
        });
    }

    public function addComment(Ticket $ticket, string $body, bool $internal = false): void
    {
        $comment = $ticket->comments()->create([
            'user_id' => Auth::id(),
            'body' => $body,
            'is_internal' => $internal,
        ]);
        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => $internal ? 'comment_internal' : 'comment',
            'data' => null,
        ]);

        // Notify participants (requester + assignee), excluding the comment author
        // Skip notification for internal comments to the requester
        $recipientIds = collect([
            $internal ? null : $ticket->requester_id,
            $ticket->assignee_id,
        ])->filter()->unique()->reject(fn ($id) => $id === Auth::id())->values();

        if ($recipientIds->isNotEmpty()) {
            $users = User::whereIn('id', $recipientIds)->where('is_active', true)->get();
            Notification::send($users, new TicketCommented($ticket, $comment));
        }
    }
}
