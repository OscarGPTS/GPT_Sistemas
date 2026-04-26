<?php

namespace App\Notifications;

use App\Models\WorkflowInstance;
use App\Models\WorkflowStep;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalRequired extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public WorkflowInstance $instance, public WorkflowStep $step)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Acción requerida: aprobar solicitud #'.$this->instance->id)
            ->view('emails.workflows.approval_required', [
                'instance' => $this->instance,
                'step' => $this->step,
                'recipient' => $notifiable,
                'url' => route('workflows.instances.show', $this->instance),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'workflow.approval_required',
            'title' => 'Aprobación requerida',
            'message' => $this->instance->workflow?->name.' · Paso: '.$this->step->name,
            'instance_id' => $this->instance->id,
            'step_id' => $this->step->id,
            'url' => route('workflows.instances.show', $this->instance),
            'icon' => 'check',
        ];
    }
}
