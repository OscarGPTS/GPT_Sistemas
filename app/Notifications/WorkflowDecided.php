<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\WorkflowInstance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkflowDecided extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public WorkflowInstance $instance,
        public User $approver,
        public string $decision,
        public ?string $comment = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $finalStatuses = ['approved', 'rejected'];
        $isFinal = in_array($this->instance->status, $finalStatuses, true);

        return (new MailMessage)
            ->subject(($isFinal ? 'Solicitud #'.$this->instance->id.' finalizada: ' : 'Actualización de solicitud #'.$this->instance->id.': ')
                .($this->decision === 'approved' ? 'Aprobada' : 'Rechazada'))
            ->view('emails.workflows.decided', [
                'instance' => $this->instance,
                'approver' => $this->approver,
                'decision' => $this->decision,
                'comment' => $this->comment,
                'isFinal' => $isFinal,
                'recipient' => $notifiable,
                'url' => route('workflows.instances.show', $this->instance),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'workflow.decided',
            'title' => $this->decision === 'approved' ? 'Solicitud aprobada' : 'Solicitud rechazada',
            'message' => $this->instance->workflow?->name.' · por '.$this->approver->name,
            'instance_id' => $this->instance->id,
            'decision' => $this->decision,
            'url' => route('workflows.instances.show', $this->instance),
            'icon' => $this->decision === 'approved' ? 'check' : 'x',
        ];
    }
}
