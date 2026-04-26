<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\KanbanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoardController extends Controller
{
    public function __construct(private KanbanService $kanban)
    {
    }

    public function show(Project $project): View
    {
        abort_unless($project->canView(auth()->user()), 403);
        $project->load([
            'manager', 'members',
            'defaultBoard.columns.tasks' => fn ($q) => $q->orderBy('position'),
            'defaultBoard.columns.tasks.assignee',
            'defaultBoard.columns.tasks.checklists.items',
        ]);

        if (! $project->defaultBoard) {
            // Auto-create the default board if missing (for legacy data)
            app(\App\Services\ProjectService::class)->createDefaultBoard($project);
            $project->load('defaultBoard.columns.tasks.assignee');
        }

        return view('projects.board', [
            'project' => $project,
            'board' => $project->defaultBoard,
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'canEdit' => $project->canEdit(auth()->user()),
        ]);
    }

    public function moveTask(Request $request, Task $task): JsonResponse
    {
        abort_unless($task->project->canView(auth()->user()), 403);

        $data = $request->validate([
            'column_id' => ['required', 'integer', 'exists:board_columns,id'],
            'position' => ['required', 'integer', 'min:1'],
        ]);

        $this->kanban->moveTask($task, (int) $data['column_id'], (int) $data['position']);

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'column_id' => $task->fresh()->column_id,
                'position' => $task->fresh()->position,
                'completed_at' => $task->fresh()->completed_at,
            ],
        ]);
    }

    public function storeColumn(Request $request, Board $board): RedirectResponse
    {
        abort_unless($board->project->canEdit(auth()->user()), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'max:16'],
        ]);
        $position = (int) ($board->columns()->max('position') ?? 0) + 1;
        $board->columns()->create(array_merge($data, ['position' => $position]));
        return back()->with('success', 'Columna agregada.');
    }

    public function updateColumn(Request $request, BoardColumn $column): RedirectResponse
    {
        abort_unless($column->board->project->canEdit(auth()->user()), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'max:16'],
            'wip_limit' => ['nullable', 'integer', 'min:1'],
            'is_done_column' => ['nullable', 'boolean'],
        ]);
        $column->update($data);
        return back()->with('success', 'Columna actualizada.');
    }

    public function destroyColumn(BoardColumn $column): RedirectResponse
    {
        abort_unless($column->board->project->canEdit(auth()->user()), 403);
        if ($column->tasks()->count() > 0) {
            return back()->withErrors(['column' => 'No se puede eliminar una columna con tareas. Muévelas primero.']);
        }
        $column->delete();
        return back()->with('success', 'Columna eliminada.');
    }
}
