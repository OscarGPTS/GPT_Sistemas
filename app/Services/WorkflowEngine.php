<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowApproval;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStep;
use App\Notifications\ApprovalRequired;
use App\Notifications\WorkflowDecided;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

class WorkflowEngine
{
    public function __construct(private AuditService $audit)
    {
    }

    public function start(Workflow $workflow, User $initiator, ?Model $subject = null, array $payload = []): WorkflowInstance
    {
        if (! $workflow->is_active) {
            throw new RuntimeException('El flujo está inactivo.');
        }
        if ($workflow->steps()->count() === 0) {
            throw new RuntimeException('El flujo no tiene pasos configurados.');
        }

        return DB::transaction(function () use ($workflow, $initiator, $subject, $payload) {
            $instance = WorkflowInstance::create([
                'workflow_id' => $workflow->id,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'initiator_id' => $initiator->id,
                'status' => 'in_review',
                'current_order' => 1,
                'payload' => $payload,
            ]);

            $this->openCurrentStepApprovals($instance);
            $this->notifyPendingApprovers($instance);
            $this->audit->log('workflow.started', $instance);

            return $instance;
        });
    }

    public function decide(WorkflowInstance $instance, User $approver, string $decision, ?string $comment = null): WorkflowInstance
    {
        if (! in_array($decision, ['approved', 'rejected'], true)) {
            throw new RuntimeException('Decisión inválida.');
        }
        if ($instance->status !== 'in_review') {
            throw new RuntimeException('El flujo no está en revisión.');
        }

        return DB::transaction(function () use ($instance, $approver, $decision, $comment) {
            $approval = $instance->approvals()
                ->where('decision', 'pending')
                ->whereHas('step', fn ($q) => $q->where('order', $instance->current_order))
                ->get()
                ->first(fn (WorkflowApproval $a) => $this->canApprove($a, $approver));

            if (! $approval) {
                throw new RuntimeException('No puede aprobar este paso.');
            }

            $approval->update([
                'approver_id' => $approver->id,
                'decision' => $decision,
                'comment' => $comment,
                'decided_at' => now(),
            ]);

            if ($decision === 'rejected') {
                $instance->update(['status' => 'rejected', 'completed_at' => now()]);
                $this->audit->log('workflow.rejected', $instance, [], ['by' => $approver->id]);
                $fresh = $instance->fresh();
                $this->notifyInitiator($fresh, $approver, $decision, $comment);
                return $fresh;
            }

            $pending = $instance->approvals()
                ->where('decision', 'pending')
                ->whereHas('step', fn ($q) => $q->where('order', $instance->current_order))
                ->count();

            if ($pending > 0 && $instance->workflow->mode === 'parallel') {
                return $instance->fresh();
            }

            $nextOrder = $instance->workflow->steps()
                ->where('order', '>', $instance->current_order)
                ->min('order');

            if ($nextOrder) {
                $instance->update(['current_order' => $nextOrder]);
                $this->openCurrentStepApprovals($instance);
                $fresh = $instance->fresh();
                $this->notifyPendingApprovers($fresh);
                $this->notifyInitiator($fresh, $approver, $decision, $comment);
                return $fresh;
            }

            $instance->update(['status' => 'approved', 'completed_at' => now()]);
            $this->audit->log('workflow.approved', $instance, [], ['by' => $approver->id]);
            $fresh = $instance->fresh();
            $this->notifyInitiator($fresh, $approver, $decision, $comment);
            return $fresh;
        });
    }

    public function cancel(WorkflowInstance $instance, ?string $reason = null): void
    {
        $instance->update([
            'status' => 'cancelled',
            'completed_at' => now(),
            'notes' => $reason,
        ]);
        $this->audit->log('workflow.cancelled', $instance, [], ['reason' => $reason]);
    }

    public function pendingForUser(User $user): Collection
    {
        return WorkflowApproval::with(['instance.workflow', 'step'])
            ->where('decision', 'pending')
            ->get()
            ->filter(fn (WorkflowApproval $a) => $this->canApprove($a, $user))
            ->filter(fn (WorkflowApproval $a) => $a->instance->status === 'in_review'
                && $a->step->order === $a->instance->current_order);
    }

    private function openCurrentStepApprovals(WorkflowInstance $instance): void
    {
        $steps = $instance->workflow->steps()->where('order', $instance->current_order)->get();
        foreach ($steps as $step) {
            foreach ($this->resolveApprovers($step, $instance) as $userId) {
                WorkflowApproval::firstOrCreate([
                    'instance_id' => $instance->id,
                    'step_id' => $step->id,
                    'approver_id' => $userId,
                ], [
                    'decision' => 'pending',
                ]);
            }
            if ($step->approver_type === 'role' && $step->role_id) {
                WorkflowApproval::firstOrCreate([
                    'instance_id' => $instance->id,
                    'step_id' => $step->id,
                    'approver_id' => null,
                ], ['decision' => 'pending']);
            }
        }
    }

    private function notifyPendingApprovers(WorkflowInstance $instance): void
    {
        $step = $instance->workflow->steps()->where('order', $instance->current_order)->first();
        if (! $step) {
            return;
        }

        $recipients = $this->resolveApproverUsers($step, $instance);
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new ApprovalRequired($instance, $step));
        }
    }

    private function notifyInitiator(WorkflowInstance $instance, User $approver, string $decision, ?string $comment): void
    {
        if ($instance->initiator) {
            $instance->initiator->notify(new WorkflowDecided($instance, $approver, $decision, $comment));
        }
    }

    private function resolveApprovers(WorkflowStep $step, WorkflowInstance $instance): array
    {
        return match ($step->approver_type) {
            'user' => $step->user_id ? [$step->user_id] : [],
            'manager' => $instance->initiator->manager_id ? [$instance->initiator->manager_id] : [],
            default => [],
        };
    }

    private function resolveApproverUsers(WorkflowStep $step, WorkflowInstance $instance): Collection
    {
        return match ($step->approver_type) {
            'user' => $step->user_id ? User::where('id', $step->user_id)->where('is_active', true)->get() : collect(),
            'manager' => $instance->initiator?->manager_id
                ? User::where('id', $instance->initiator->manager_id)->where('is_active', true)->get()
                : collect(),
            'role' => $step->role_id
                ? User::where('is_active', true)->whereHas('roles', fn ($q) => $q->where('roles.id', $step->role_id))->get()
                : collect(),
            default => collect(),
        };
    }

    private function canApprove(WorkflowApproval $approval, User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if ($approval->approver_id && $approval->approver_id === $user->id) {
            return true;
        }
        $step = $approval->step;
        if ($step->approver_type === 'role' && $step->role_id) {
            return $user->roles()->where('roles.id', $step->role_id)->exists();
        }
        if ($step->approver_type === 'manager') {
            return $approval->instance->initiator->manager_id === $user->id;
        }
        if ($step->approver_type === 'user') {
            return $step->user_id === $user->id;
        }
        return false;
    }
}
