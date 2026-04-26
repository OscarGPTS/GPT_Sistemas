<?php

namespace App\Http\Controllers;

use App\Models\ProjectRequest;
use App\Services\ProjectRequestService;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectRequestController extends Controller
{
    public function __construct(
        private ProjectRequestService $service,
        private ProjectService $projects,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = ProjectRequest::with(['requester', 'project', 'workflowInstance']);

        if (! $user->isAdmin() && ! $user->hasRole(['it', 'auditor', 'hr', 'manager'])) {
            $query->where('requester_id', $user->id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return view('project_requests.index', [
            'requests' => $query->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only('status'),
        ]);
    }

    public function create(): View
    {
        return view('project_requests.form', [
            'request' => new ProjectRequest(['priority' => 'medium', 'impact' => 'medium']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRequest($request);
        $data['requester_id'] = $request->user()->id;
        $data['status'] = $request->input('action') === 'submit' ? 'in_review' : 'draft';
        $req = ProjectRequest::create($data);

        if ($request->input('action') === 'submit') {
            $this->service->submit($req);
            return redirect()->route('project_requests.show', $req)->with('success', 'Solicitud enviada a aprobación.');
        }

        return redirect()->route('project_requests.show', $req)->with('success', 'Borrador guardado.');
    }

    public function show(ProjectRequest $projectRequest): View
    {
        $user = auth()->user();
        if (! $user->isAdmin()
            && ! $user->hasRole(['it', 'auditor', 'hr', 'manager'])
            && $projectRequest->requester_id !== $user->id) {
            abort(403);
        }
        $projectRequest->load(['requester', 'workflowInstance.workflow.steps', 'workflowInstance.approvals.step', 'workflowInstance.approvals.approver', 'project']);
        return view('project_requests.show', ['request' => $projectRequest]);
    }

    public function submit(ProjectRequest $projectRequest): RedirectResponse
    {
        abort_unless(
            auth()->id() === $projectRequest->requester_id || auth()->user()->isAdmin(),
            403
        );
        $this->service->submit($projectRequest);
        return back()->with('success', 'Solicitud enviada a aprobación.');
    }

    public function convert(ProjectRequest $projectRequest): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->hasRole(['manager']), 403);
        $project = $this->projects->convertRequestToProject($projectRequest);
        return redirect()->route('projects.board', $project)->with('success', 'Proyecto creado a partir de la solicitud.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'justification' => ['nullable', 'string'],
            'impact' => ['required', 'in:low,medium,high'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'budget_estimate' => ['nullable', 'numeric', 'min:0'],
            'expected_area' => ['nullable', 'string', 'max:100'],
            'desired_start_date' => ['nullable', 'date'],
            'desired_end_date' => ['nullable', 'date', 'after_or_equal:desired_start_date'],
        ]);
    }
}
