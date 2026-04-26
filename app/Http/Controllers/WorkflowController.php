<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowInstance;
use App\Services\WorkflowEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkflowController extends Controller
{
    public function __construct(private WorkflowEngine $engine)
    {
    }

    public function index(): View
    {
        return view('workflows.index', [
            'workflows' => Workflow::withCount('steps', 'instances')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('workflows.form', [
            'workflow' => new Workflow(['mode' => 'sequential', 'is_active' => true]),
            'roles' => Role::orderBy('label')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateWorkflow($request);
        $workflow = Workflow::create($data);
        $this->syncSteps($workflow, $request->input('steps', []));
        return redirect()->route('workflows.show', $workflow)->with('success', 'Flujo creado.');
    }

    public function show(Workflow $workflow): View
    {
        $workflow->load('steps.role', 'steps.user');
        $instances = $workflow->instances()->with('initiator')->latest()->paginate(15);
        return view('workflows.show', compact('workflow', 'instances'));
    }

    public function edit(Workflow $workflow): View
    {
        $workflow->load('steps');
        return view('workflows.form', [
            'workflow' => $workflow,
            'roles' => Role::orderBy('label')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Workflow $workflow): RedirectResponse
    {
        $data = $this->validateWorkflow($request, $workflow);
        $workflow->update($data);
        $this->syncSteps($workflow, $request->input('steps', []));
        return redirect()->route('workflows.show', $workflow)->with('success', 'Flujo actualizado.');
    }

    public function destroy(Workflow $workflow): RedirectResponse
    {
        $workflow->delete();
        return redirect()->route('workflows.index')->with('success', 'Flujo eliminado.');
    }

    public function myApprovals(Request $request): View
    {
        $approvals = $this->engine->pendingForUser($request->user());
        return view('workflows.my_approvals', compact('approvals'));
    }

    public function instances(Request $request): View
    {
        $query = WorkflowInstance::with(['workflow', 'initiator']);
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        return view('workflows.instances', [
            'instances' => $query->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only('status'),
        ]);
    }

    public function showInstance(WorkflowInstance $instance): View
    {
        $instance->load(['workflow.steps', 'initiator', 'approvals.approver', 'approvals.step']);
        return view('workflows.instance', compact('instance'));
    }

    public function startInstance(Request $request, Workflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);
        $instance = $this->engine->start(
            $workflow,
            $request->user(),
            null,
            $data
        );
        return redirect()->route('workflows.instances.show', $instance)
            ->with('success', 'Solicitud iniciada.');
    }

    public function decide(Request $request, WorkflowInstance $instance): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'comment' => ['nullable', 'string'],
        ]);
        $this->engine->decide($instance, $request->user(), $data['decision'], $data['comment'] ?? null);
        return back()->with('success', 'Decisión registrada.');
    }

    private function validateWorkflow(Request $request, ?Workflow $workflow = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150',
                'unique:workflows,slug'.($workflow ? ','.$workflow->id : '')],
            'target_type' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'mode' => ['required', 'in:sequential,parallel'],
            'is_active' => ['boolean'],
        ]);
    }

    private function syncSteps(Workflow $workflow, array $steps): void
    {
        $workflow->steps()->delete();
        foreach (array_values($steps) as $i => $step) {
            if (empty($step['name'] ?? null)) {
                continue;
            }
            $workflow->steps()->create([
                'order' => $i + 1,
                'name' => $step['name'],
                'approver_type' => $step['approver_type'] ?? 'role',
                'role_id' => $step['approver_type'] === 'role' ? ($step['role_id'] ?? null) : null,
                'user_id' => $step['approver_type'] === 'user' ? ($step['user_id'] ?? null) : null,
                'is_optional' => ! empty($step['is_optional']),
                'allow_parallel' => ! empty($step['allow_parallel']),
                'instructions' => $step['instructions'] ?? null,
            ]);
        }
    }
}
