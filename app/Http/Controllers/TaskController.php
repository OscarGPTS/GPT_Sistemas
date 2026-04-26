<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\BoardColumn;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskChecklist;
use App\Models\TaskChecklistItem;
use App\Models\TaskHistory;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function show(Task $task): View
    {
        abort_unless($task->project->canView(auth()->user()), 403);
        $task->load([
            'project', 'board.columns', 'column', 'assignee', 'creator',
            'comments.user', 'attachments.uploader', 'checklists.items',
            'history.user', 'ticket', 'asset',
        ]);
        return view('tasks.show', [
            'task' => $task,
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'canEdit' => $task->project->canEdit(auth()->user()) || $task->assignee_id === auth()->id(),
        ]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        abort_unless($project->canEdit(auth()->user()) || auth()->user()->hasRole(['it']), 403);

        $data = $request->validate([
            'column_id' => ['required', 'exists:board_columns,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'due_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:30'],
            'ticket_id' => ['nullable', 'exists:tickets,id'],
            'asset_id' => ['nullable', 'exists:assets,id'],
        ]);

        $column = BoardColumn::findOrFail($data['column_id']);
        $position = (int) (Task::where('column_id', $column->id)->max('position') ?? 0) + 1;

        $task = Task::create(array_merge($data, [
            'project_id' => $project->id,
            'board_id' => $column->board_id,
            'position' => $position,
            'created_by' => auth()->id(),
        ]));

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'data' => ['priority' => $task->priority, 'column' => $column->name],
        ]);

        if ($task->assignee_id && $task->assignee_id !== auth()->id()) {
            $task->assignee?->notify(new TaskAssigned($task));
        }

        return redirect()->route('projects.board', $project)->with('success', 'Tarea creada.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        abort_unless($task->project->canEdit(auth()->user()) || $task->assignee_id === auth()->id(), 403);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'due_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:30'],
            'ticket_id' => ['nullable', 'exists:tickets,id'],
            'asset_id' => ['nullable', 'exists:assets,id'],
        ]);

        $oldAssignee = $task->assignee_id;
        $task->update(array_filter($data, fn ($v) => $v !== null) + ['tags' => $data['tags'] ?? $task->tags]);

        if (! empty($data['assignee_id']) && $data['assignee_id'] !== $oldAssignee && $data['assignee_id'] !== auth()->id()) {
            $task->fresh()->assignee?->notify(new TaskAssigned($task));
            TaskHistory::create([
                'task_id' => $task->id, 'user_id' => auth()->id(), 'action' => 'assigned',
                'data' => ['from' => $oldAssignee, 'to' => $data['assignee_id']],
            ]);
        }

        return back()->with('success', 'Tarea actualizada.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        abort_unless($task->project->canEdit(auth()->user()), 403);
        $project = $task->project;
        $task->delete();
        return redirect()->route('projects.board', $project)->with('success', 'Tarea eliminada.');
    }

    public function comment(Request $request, Task $task): RedirectResponse
    {
        abort_unless($task->project->canView(auth()->user()), 403);
        $data = $request->validate(['body' => ['required', 'string']]);
        $task->comments()->create([
            'user_id' => auth()->id(),
            'body' => $data['body'],
        ]);
        TaskHistory::create([
            'task_id' => $task->id, 'user_id' => auth()->id(), 'action' => 'commented',
        ]);
        return back()->with('success', 'Comentario agregado.');
    }

    public function uploadAttachment(Request $request, Task $task): RedirectResponse
    {
        abort_unless($task->project->canView(auth()->user()), 403);
        $request->validate(['file' => ['required', 'file', 'max:10240']]);
        $file = $request->file('file');
        $path = $file->store('tasks/'.$task->id);
        $task->attachments()->create([
            'uploaded_by' => auth()->id(),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
        return back()->with('success', 'Archivo adjuntado.');
    }

    public function downloadAttachment(int $id)
    {
        $att = \App\Models\TaskAttachment::with('task.project')->findOrFail($id);
        abort_unless($att->task->project->canView(auth()->user()), 403);
        return Storage::download($att->path, $att->original_name);
    }

    public function storeChecklist(Request $request, Task $task): RedirectResponse
    {
        abort_unless($task->project->canEdit(auth()->user()) || $task->assignee_id === auth()->id(), 403);
        $data = $request->validate(['title' => ['required', 'string', 'max:150']]);
        $position = (int) ($task->checklists()->max('position') ?? 0) + 1;
        $task->checklists()->create(['title' => $data['title'], 'position' => $position]);
        return back()->with('success', 'Lista de verificación creada.');
    }

    public function destroyChecklist(TaskChecklist $checklist): RedirectResponse
    {
        abort_unless($checklist->task->project->canEdit(auth()->user()), 403);
        $checklist->delete();
        return back()->with('success', 'Lista eliminada.');
    }

    public function storeChecklistItem(Request $request, TaskChecklist $checklist): RedirectResponse
    {
        abort_unless($checklist->task->project->canView(auth()->user()), 403);
        $data = $request->validate(['text' => ['required', 'string', 'max:200']]);
        $position = (int) ($checklist->items()->max('position') ?? 0) + 1;
        $checklist->items()->create(['text' => $data['text'], 'position' => $position]);
        return back();
    }

    public function toggleChecklistItem(TaskChecklistItem $item): JsonResponse|RedirectResponse
    {
        abort_unless($item->checklist->task->project->canView(auth()->user()), 403);
        $item->update(['is_done' => ! $item->is_done]);
        if (request()->expectsJson()) {
            return response()->json(['is_done' => $item->is_done]);
        }
        return back();
    }

    public function destroyChecklistItem(TaskChecklistItem $item): RedirectResponse
    {
        abort_unless($item->checklist->task->project->canView(auth()->user()), 403);
        $item->delete();
        return back();
    }
}
