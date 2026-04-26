@extends('layouts.app')
@section('title', 'Tablero · '.$project->name)
@section('page-title', $project->name)
@section('page-subtitle', 'Tablero Kanban del proyecto')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@endpush

@section('content')
@php
    $priorityColors = [
        'urgent' => 'bg-red-500',
        'high'   => 'bg-orange-500',
        'medium' => 'bg-amber-500',
        'low'    => 'bg-slate-400',
    ];
    $priorityLabels = ['urgent'=>'Urgente','high'=>'Alta','medium'=>'Media','low'=>'Baja'];
@endphp

<!-- Project header -->
<div class="bg-white rounded-xl shadow-card border border-slate-200 p-5 mb-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white font-semibold flex-shrink-0" style="background: {{ $project->color }}">
                {{ strtoupper(substr($project->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono text-xs text-slate-400">{{ $project->code }}</span>
                    @php
                        $statusCls = match($project->status) {
                            'planning' => 'bg-slate-100 text-slate-600 border-slate-200',
                            'in_progress' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'paused' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                            default => 'bg-slate-100',
                        };
                        $statusLabel = ['planning'=>'Planeación','in_progress'=>'En curso','paused'=>'Pausado','completed'=>'Finalizado','cancelled'=>'Cancelado'][$project->status] ?? $project->status;
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusCls }}">{{ $statusLabel }}</span>
                </div>
                <h1 class="text-xl font-bold text-slate-900 mt-0.5">{{ $project->name }}</h1>
                <div class="text-sm text-slate-500 mt-1">
                    @if($project->manager) <b>Manager:</b> {{ $project->manager->name }} · @endif
                    @if($project->area) <b>Área:</b> {{ $project->area }} · @endif
                    {{ $project->members->count() }} {{ Str::plural('miembro', $project->members->count()) }}
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($canEdit)
                <button @click="document.getElementById('new-task-modal').showModal()"
                        class="inline-flex items-center gap-1.5 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Nueva tarea
                </button>
                <a href="{{ route('projects.edit', $project) }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-3.5 py-2 rounded-lg text-sm font-medium">Editar</a>
            @endif
            <a href="{{ route('projects.show', $project) }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-3.5 py-2 rounded-lg text-sm font-medium">Detalles</a>
        </div>
    </div>
</div>

<!-- Board -->
<div class="flex gap-4 overflow-x-auto pb-4 scrollbar-thin -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8" id="kanban-board">
    @foreach($board->columns as $column)
        <div class="flex-shrink-0 w-80 bg-slate-100 rounded-xl flex flex-col" style="max-height: calc(100vh - 220px);">
            <!-- Column header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background: {{ $column->color }}"></span>
                    <span class="font-semibold text-slate-800 text-sm truncate">{{ $column->name }}</span>
                    <span class="bg-slate-200 text-slate-600 text-xs px-2 py-0.5 rounded-full task-count">{{ $column->tasks->count() }}</span>
                </div>
                @if($canEdit)
                <button @click="document.getElementById('new-task-modal').showModal(); document.getElementById('new-task-column').value = {{ $column->id }};"
                        class="text-slate-400 hover:text-brand-600 transition" title="Agregar tarea">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </button>
                @endif
            </div>

            <!-- Tasks list (sortable) -->
            <div class="flex-1 overflow-y-auto p-2 space-y-2 kanban-column"
                 data-column-id="{{ $column->id }}">
                @foreach($column->tasks as $task)
                    @php
                        $pColor = $priorityColors[$task->priority] ?? 'bg-slate-400';
                        $checklistTotal = $task->checklists->sum(fn ($c) => $c->items->count());
                        $checklistDone = $task->checklists->sum(fn ($c) => $c->items->where('is_done', true)->count());
                        $isOverdue = $task->due_at && $task->due_at->isPast() && ! $task->completed_at;
                    @endphp
                    <a href="{{ route('tasks.show', $task) }}"
                       data-task-id="{{ $task->id }}"
                       class="kanban-task block bg-white rounded-lg shadow-sm hover:shadow-md transition border border-slate-200 cursor-grab active:cursor-grabbing">
                        <div class="p-3">
                            <div class="flex items-start gap-2 mb-1.5">
                                <div class="w-1 h-5 rounded-full {{ $pColor }} flex-shrink-0 mt-0.5"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[11px] font-mono text-slate-400">{{ $task->code }}</div>
                                    <div class="font-medium text-slate-900 text-sm leading-snug">{{ $task->title }}</div>
                                </div>
                            </div>

                            @if($task->tags && count($task->tags) > 0)
                                <div class="flex flex-wrap gap-1 mb-2">
                                    @foreach($task->tags as $tag)
                                        <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex items-center justify-between mt-2 text-xs">
                                <div class="flex items-center gap-2 text-slate-500">
                                    @if($checklistTotal > 0)
                                        <span class="flex items-center gap-0.5">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75"/></svg>
                                            {{ $checklistDone }}/{{ $checklistTotal }}
                                        </span>
                                    @endif
                                    @if($task->due_at)
                                        <span class="flex items-center gap-0.5 {{ $isOverdue ? 'text-red-600 font-semibold' : '' }}">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                            {{ $task->due_at->format('d/m') }}
                                        </span>
                                    @endif
                                    @if($task->comments->count() > 0)
                                        <span class="flex items-center gap-0.5">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
                                            {{ $task->comments->count() }}
                                        </span>
                                    @endif
                                </div>
                                @if($task->assignee)
                                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-[10px] font-bold" title="{{ $task->assignee->name }}">
                                        {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

@if($canEdit)
<!-- New task modal (native dialog) -->
<dialog id="new-task-modal" class="rounded-xl shadow-2xl backdrop:bg-slate-900/50 p-0 w-full max-w-2xl">
    <form method="POST" action="{{ route('tasks.store', $project) }}" class="bg-white">
        @csrf
        <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-semibold text-slate-900">Nueva tarea</h3>
            <button type="button" onclick="document.getElementById('new-task-modal').close()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-5 space-y-4">
            @php
                $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
                $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
            @endphp
            <div>
                <label class="{{ $label }}">Título *</label>
                <input name="title" required class="{{ $input }}" placeholder="Ej. Diseñar mockups iniciales">
            </div>
            <div>
                <label class="{{ $label }}">Descripción</label>
                <textarea name="description" rows="3" class="{{ $input }}"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $label }}">Columna *</label>
                    <select name="column_id" id="new-task-column" required class="{{ $input }}">
                        @foreach($board->columns as $col)
                            <option value="{{ $col->id }}">{{ $col->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}">Prioridad *</label>
                    <select name="priority" class="{{ $input }}">
                        <option value="low">Baja</option>
                        <option value="medium" selected>Media</option>
                        <option value="high">Alta</option>
                        <option value="urgent">Urgente</option>
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}">Responsable</label>
                    <select name="assignee_id" class="{{ $input }}">
                        <option value="">— Sin asignar —</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}">Fecha límite</label>
                    <input type="date" name="due_at" class="{{ $input }}">
                </div>
            </div>
        </div>
        <div class="px-5 py-3 border-t border-slate-100 flex justify-end gap-2 bg-slate-50">
            <button type="button" onclick="document.getElementById('new-task-modal').close()" class="px-4 py-2 text-slate-600 text-sm">Cancelar</button>
            <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 text-white px-5 py-2 rounded-lg text-sm font-medium">Crear tarea</button>
        </div>
    </form>
</dialog>
@endif

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function() {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const moveUrl = (taskId) => `{{ url('tasks') }}/${taskId}/move`;

    document.querySelectorAll('.kanban-column').forEach(col => {
        new Sortable(col, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'opacity-40',
            dragClass: 'rotate-2',
            forceFallback: true,
            fallbackClass: 'kanban-drag',
            @if(! $canEdit) disabled: true, @endif
            onEnd: async (evt) => {
                const taskEl = evt.item;
                const taskId = taskEl.dataset.taskId;
                const toCol = evt.to;
                const newColumnId = toCol.dataset.columnId;
                const newPosition = Array.from(toCol.children).indexOf(taskEl) + 1;

                try {
                    const resp = await fetch(moveUrl(taskId), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ column_id: newColumnId, position: newPosition }),
                    });
                    if (! resp.ok) throw new Error('Error');
                    // Update task counts
                    document.querySelectorAll('.kanban-column').forEach(c => {
                        const count = c.children.length;
                        const header = c.previousElementSibling;
                        const badge = header && header.querySelector('.task-count');
                        if (badge) badge.textContent = count;
                    });
                } catch (e) {
                    console.error(e);
                    alert('No se pudo mover la tarea. Recarga la página e intenta de nuevo.');
                    location.reload();
                }
            },
        });
    });
})();
</script>
<style>
.kanban-drag { transform: rotate(2deg); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
.kanban-column.sortable-ghost { background: rgba(99,102,241,0.04); }
</style>
@endsection
