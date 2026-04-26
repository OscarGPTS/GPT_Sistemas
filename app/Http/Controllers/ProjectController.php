<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Project::with('manager', 'members')->withCount('tasks');

        // Restrict to user's projects unless admin
        if (! $user->isAdmin() && ! $user->hasRole(['it', 'auditor'])) {
            $query->where(function ($q) use ($user) {
                $q->where('manager_id', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('members', fn ($m) => $m->where('users.id', $user->id));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%");
            });
        }

        $projects = $query->latest()->paginate(20)->withQueryString();
        return view('projects.index', [
            'projects' => $projects,
            'filters' => $request->only(['status', 'q']),
        ]);
    }

    public function create(): View
    {
        return view('projects.form', [
            'project' => new Project(['status' => 'planning', 'priority' => 'medium', 'color' => '#6366f1']),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProject($request);
        $members = $data['members'] ?? [];
        unset($data['members']);
        $project = $this->service->createProject($data);
        if (! empty($members)) {
            $this->service->syncMembers($project, $members);
        }
        return redirect()->route('projects.board', $project)->with('success', 'Proyecto creado.');
    }

    public function show(Project $project): View
    {
        abort_unless($project->canView(auth()->user()), 403);
        $project->load(['manager', 'creator', 'members', 'boards.columns', 'request', 'tasks.assignee']);
        $tasksByStatus = $project->tasks->groupBy('column.name')->map->count();

        return view('projects.show', compact('project', 'tasksByStatus'));
    }

    public function edit(Project $project): View
    {
        abort_unless($project->canEdit(auth()->user()), 403);
        return view('projects.form', [
            'project' => $project,
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        abort_unless($project->canEdit(auth()->user()), 403);
        $data = $this->validateProject($request, $project);
        $members = $data['members'] ?? [];
        unset($data['members']);
        $project->update($data);
        $this->service->syncMembers($project, $members);
        return redirect()->route('projects.show', $project)->with('success', 'Proyecto actualizado.');
    }

    public function archive(Project $project): RedirectResponse
    {
        abort_unless($project->canEdit(auth()->user()), 403);
        $this->service->archiveProject($project);
        return redirect()->route('projects.index')->with('success', 'Proyecto archivado.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Proyecto eliminado.');
    }

    private function validateProject(Request $request, ?Project $project = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'area' => ['nullable', 'string', 'max:100'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:planning,in_progress,paused,completed,cancelled'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'color' => ['nullable', 'string', 'max:16'],
            'members' => ['nullable', 'array'],
            'members.*' => ['in:manager,collaborator,observer'],
        ]);
        return $data;
    }
}
