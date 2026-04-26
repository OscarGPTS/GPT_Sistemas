@extends('layouts.app')
@section('title', $task->code.' · '.$task->title)
@section('page-title', $task->title)
@section('page-subtitle', $task->code.' · '.$task->project->name)

@section('content')
@php
    $priorityColors = ['urgent'=>'#dc2626','high'=>'#ea580c','medium'=>'#d97706','low'=>'#64748b'];
    $accent = $priorityColors[$task->priority] ?? '#4f46e5';
    $priorityLabels = ['urgent'=>'Urgente','high'=>'Alta','medium'=>'Media','low'=>'Baja'];
@endphp

<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden mb-5">
    <div class="h-1.5" style="background: {{ $accent }}"></div>
    <div class="p-5 flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-1">
                <span class="font-mono text-xs text-slate-400">{{ $task->code }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">{{ $task->column?->name }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full" style="background: {{ $accent }}15; color: {{ $accent }}">{{ $priorityLabels[$task->priority] ?? $task->priority }}</span>
                @if($task->isOverdue())
                    <span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200">Vencida</span>
                @elseif($task->completed_at)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Completada</span>
                @endif
            </div>
            <h1 class="text-xl font-bold text-slate-900">{{ $task->title }}</h1>
            <a href="{{ route('projects.board', $task->project) }}" class="text-xs text-brand-600 hover:underline">← Volver al tablero</a>
        </div>
        @if($canEdit)
            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('¿Eliminar tarea?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:text-red-700 text-sm font-medium">Eliminar tarea</button>
            </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        <!-- Description -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-slate-900">Descripción</h3>
                @if($canEdit)
                <button onclick="document.getElementById('edit-desc').classList.toggle('hidden'); this.classList.toggle('hidden');" class="text-xs text-brand-600 hover:underline">Editar</button>
                @endif
            </div>
            <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $task->description ?: '—' }}</p>
            @if($canEdit)
                <form id="edit-desc" method="POST" action="{{ route('tasks.update', $task) }}" class="hidden mt-3 space-y-2">
                    @csrf @method('PUT')
                    <textarea name="description" rows="4" class="w-full border border-slate-300 rounded-lg p-2 text-sm">{{ $task->description }}</textarea>
                    <button class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-1.5 rounded text-sm">Guardar</button>
                </form>
            @endif
        </div>

        <!-- Checklists -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Listas de verificación</h3>
            @foreach($task->checklists as $cl)
                @php
                    $total = $cl->items->count();
                    $done = $cl->items->where('is_done', true)->count();
                    $pct = $total ? round(($done/$total)*100) : 0;
                @endphp
                <div class="mb-4 last:mb-0">
                    <div class="flex justify-between items-center mb-1">
                        <div class="font-medium text-sm text-slate-800">{{ $cl->title }}</div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500">{{ $done }}/{{ $total }}</span>
                            @if($canEdit)
                            <form method="POST" action="{{ route('checklists.destroy', $cl) }}" class="inline" onsubmit="return confirm('¿Eliminar lista?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-500 hover:text-red-700">×</button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @if($total > 0)
                        <div class="w-full bg-slate-100 rounded-full h-1.5 mb-2"><div class="h-1.5 bg-emerald-500 rounded-full transition-all" style="width: {{ $pct }}%"></div></div>
                    @endif
                    <ul class="space-y-1.5">
                        @foreach($cl->items as $item)
                            <li class="flex items-center gap-2 group">
                                <form method="POST" action="{{ route('checklist_items.toggle', $item) }}" class="flex items-center gap-2 flex-1">
                                    @csrf
                                    <button type="submit" class="w-5 h-5 rounded border-2 {{ $item->is_done ? 'bg-emerald-500 border-emerald-500' : 'border-slate-300 hover:border-brand-500' }} flex items-center justify-center transition flex-shrink-0">
                                        @if($item->is_done)<svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>@endif
                                    </button>
                                    <span class="text-sm flex-1 {{ $item->is_done ? 'line-through text-slate-400' : 'text-slate-700' }}">{{ $item->text }}</span>
                                </form>
                                <form method="POST" action="{{ route('checklist_items.destroy', $item) }}" class="opacity-0 group-hover:opacity-100">
                                    @csrf @method('DELETE')
                                    <button class="text-slate-300 hover:text-red-500">×</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                    <form method="POST" action="{{ route('checklists.items.store', $cl) }}" class="mt-2 flex gap-2">
                        @csrf
                        <input name="text" required placeholder="Agregar elemento..." class="flex-1 border border-slate-200 rounded px-2 py-1 text-sm">
                        <button class="text-xs text-brand-600 hover:text-brand-700 font-medium">+ Añadir</button>
                    </form>
                </div>
            @endforeach
            @if($canEdit)
                <form method="POST" action="{{ route('tasks.checklists.store', $task) }}" class="flex gap-2 pt-3 border-t border-slate-100 mt-3">
                    @csrf
                    <input name="title" required placeholder="Nombre de la nueva lista..." class="flex-1 border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                    <button class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-medium">+ Crear lista</button>
                </form>
            @endif
        </div>

        <!-- Comments -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Conversación</h3>
            <div class="space-y-3 mb-4">
                @forelse($task->comments as $c)
                    <div class="flex gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">{{ strtoupper(substr($c->user?->name ?? '?', 0, 1)) }}</div>
                        <div class="flex-1 bg-slate-50 border border-slate-200 rounded-lg p-3">
                            <div class="flex justify-between text-xs text-slate-500 mb-1">
                                <b class="text-slate-700">{{ $c->user?->name }}</b>
                                <span>{{ $c->created_at?->diffForHumans() }}</span>
                            </div>
                            <div class="text-sm text-slate-700 whitespace-pre-wrap">{{ $c->body }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-400 text-center py-4">Aún no hay comentarios.</div>
                @endforelse
            </div>
            <form method="POST" action="{{ route('tasks.comment', $task) }}" class="space-y-2">
                @csrf
                <textarea name="body" rows="2" required class="w-full border border-slate-300 rounded-lg p-2 text-sm" placeholder="Escribe un comentario..."></textarea>
                <button class="bg-gradient-to-r from-brand-600 to-brand-700 text-white px-4 py-1.5 rounded-lg text-sm font-medium">Comentar</button>
            </form>
        </div>

        <!-- Attachments -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Adjuntos</h3>
            @if($task->attachments->count())
                <ul class="divide-y divide-slate-100 mb-3">
                    @foreach($task->attachments as $att)
                        <li class="py-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32"/></svg>
                            <a href="{{ route('tasks.attachment', $att->id) }}" class="text-sm text-brand-600 hover:underline flex-1">{{ $att->original_name }}</a>
                            <span class="text-xs text-slate-400">{{ number_format(($att->size ?? 0)/1024, 0) }} KB</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            <form method="POST" action="{{ route('tasks.attachments.store', $task) }}" enctype="multipart/form-data" class="flex gap-2">
                @csrf
                <input type="file" name="file" required class="text-sm flex-1">
                <button class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-medium">+ Subir</button>
            </form>
        </div>

        <!-- History -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Historial</h3>
            <ul class="space-y-1.5 text-xs text-slate-600">
                @foreach($task->history as $h)
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300 mt-1.5 flex-shrink-0"></span>
                        <span class="text-slate-400 font-mono">{{ $h->created_at?->format('d/m H:i') }}</span>
                        <span><b class="text-slate-700">{{ $h->user?->name ?? 'Sistema' }}</b> {{ $h->action }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-5">
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-4">Detalles</h3>
            <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase">Responsable</label>
                    <select name="assignee_id" {{ $canEdit ? '' : 'disabled' }} class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                        <option value="">— Sin asignar —</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected($task->assignee_id === $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase">Prioridad</label>
                    <select name="priority" {{ $canEdit ? '' : 'disabled' }} class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                        @foreach(['low'=>'Baja','medium'=>'Media','high'=>'Alta','urgent'=>'Urgente'] as $k=>$v)
                            <option value="{{ $k }}" @selected($task->priority === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase">Fecha límite</label>
                    <input type="datetime-local" name="due_at" value="{{ optional($task->due_at)->format('Y-m-d\TH:i') }}" {{ $canEdit ? '' : 'disabled' }} class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
                </div>
                @if($canEdit)
                    <button class="w-full bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 text-white py-1.5 rounded-lg text-sm font-medium">Actualizar</button>
                @endif
            </form>
        </div>

        @if($task->ticket || $task->asset)
            <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">Vinculación</h3>
                @if($task->ticket)
                    <a href="{{ route('tickets.show', $task->ticket) }}" class="block bg-rose-50 border border-rose-200 rounded-lg p-2 mb-2 hover:bg-rose-100 text-sm">
                        <div class="font-mono text-xs text-rose-600">🎫 {{ $task->ticket->code }}</div>
                        <div class="text-slate-700 truncate">{{ $task->ticket->subject }}</div>
                    </a>
                @endif
                @if($task->asset)
                    <a href="{{ route('assets.show', $task->asset) }}" class="block bg-emerald-50 border border-emerald-200 rounded-lg p-2 hover:bg-emerald-100 text-sm">
                        <div class="font-mono text-xs text-emerald-600">📦 {{ $task->asset->internal_code }}</div>
                        <div class="text-slate-700 truncate">{{ $task->asset->brand }} {{ $task->asset->model }}</div>
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
