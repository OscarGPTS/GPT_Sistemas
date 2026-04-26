<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('['.$this->task->code.'] Tarea asignada: '.$this->task->title)
            ->view('emails.tasks.assigned', [
                'task' => $this->task,
                'recipient' => $notifiable,
                'url' => route('projects.board', $this->task->project) . '#task-' . $this->task->id,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task.assigned',
            'title' => 'Tarea asignada',
            'message' => $this->task->code.' · '.$this->task->title,
            'task_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'url' => route('projects.board', $this->task->project),
            'icon' => 'check',
        ];
    }
}
