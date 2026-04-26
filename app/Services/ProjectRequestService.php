<?php

namespace App\Services;

use App\Models\ProjectRequest;
use App\Models\Workflow;
use App\Models\WorkflowInstance;
use Illuminate\Support\Facades\DB;

class ProjectRequestService
{
    public function __construct(
        private WorkflowEngine $engine,
        private ProjectService $projects,
        private AuditService $audit,
    ) {
    }

    public function submit(ProjectRequest $request): ProjectRequest
    {
        if ($request->status !== 'draft' && $request->status !== 'rejected') {
            throw new \RuntimeException('Esta solicitud ya fue enviada.');
        }

        $workflow = Workflow::where('slug', 'project-request')->where('is_active', true)->first();
        if (! $workflow) {
            throw new \RuntimeException('No hay flujo activo para solicitudes de proyecto.');
        }

        return DB::transaction(function () use ($request, $workflow) {
            $instance = $this->engine->start($workflow, $request->requester, $request, [
                'project_request_id' => $request->id,
                'name' => $request->name,
                'priority' => $request->priority,
                'budget_estimate' => $request->budget_estimate,
                'impact' => $request->impact,
            ]);

            $request->update([
                'status' => 'in_review',
                'workflow_instance_id' => $instance->id,
                'submitted_at' => now(),
            ]);

            $this->audit->log('project_request.submitted', $request);
            return $request->fresh();
        });
    }

    /**
     * Called by the workflow engine when an instance subject is a ProjectRequest and it ends.
     * We hook into model events on WorkflowInstance via boot or invoke this manually.
     */
    public function syncFromInstance(WorkflowInstance $instance): void
    {
        if ($instance->subject_type !== ProjectRequest::class) {
            return;
        }
        $request = ProjectRequest::find($instance->subject_id);
        if (! $request) {
            return;
        }

        DB::transaction(function () use ($instance, $request) {
            $newStatus = match ($instance->status) {
                'approved' => 'approved',
                'rejected' => 'rejected',
                'cancelled' => 'cancelled',
                default => $request->status,
            };
            if ($newStatus === $request->status) {
                return;
            }
            $request->update([
                'status' => $newStatus,
                'decided_at' => now(),
            ]);

            // Auto-convert to project if approved
            if ($newStatus === 'approved' && ! $request->project_id) {
                $this->projects->convertRequestToProject($request->fresh());
            }
        });
    }
}
