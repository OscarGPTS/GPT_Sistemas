@extends('layouts.app')
@section('title', $project->name)
@section('page-title', $project->name)
@section('page-subtitle', 'Detalle del proyecto')

@section('content')
<!-- Header -->
<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden mb-5">
    <div class="h-2" style="background: {{ $project->color }}"></div>
    <div class="p-5 flex flex-wrap items-start justify-between gap-3">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold flex-shrink-0" style="background: {{ $project->color }}">
                {{ strtoupper(substr($project->name, 0, 2)) }}
            </div>
            <div>
                <span class="font-mono text-xs text-slate-400">{{ $project->code }}</span>
                <h1 class="text-2xl font-bold text-slate-900">{{ $project->name }}</h1>
                <div class="text-sm text-slate-500 mt-1">{{ $project->area }} · creado por {{ $project->creator?->name ?? '—' }}</div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('projects.board', $project) }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5"/></svg>
                Abrir tablero
            </a>
            @if($project->canEdit(auth()->user()))
                <a href="{{ route('projects.edit', $project) }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-3.5 py-2 rounded-lg text-sm font-medium">Editar</a>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Información</h3>
            @if($project->description)
                <p class="text-sm text-slate-700 whitespace-pre-wrap mb-4">{{ $project->description }}</p>
            @endif
            <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                @php
                    $statusLabel = ['planning'=>'Planeación','in_progress'=>'En curso','paused'=>'Pausado','completed'=>'Finalizado','cancelled'=>'Cancelado'][$project->status] ?? $project->status;
                @endphp
                <div><dt class="text-xs text-slate-500 uppercase">Estado</dt><dd class="font-medium">{{ $statusLabel }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">Prioridad</dt><dd class="font-medium capitalize">{{ $project->priority }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">Manager</dt><dd>{{ $project->manager?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">Inicio</dt><dd>{{ $project->start_date?->format('d/m/Y') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">Fin</dt><dd>{{ $project->end_date?->format('d/m/Y') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">Presupuesto</dt><dd>{{ $project->budget ? '$ '.number_format((float) $project->budget, 2) : '—' }}</dd></div>
            </dl>
        </div>

        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Distribución de tareas</h3>
            @if($tasksByStatus->isEmpty())
                <div class="text-sm text-slate-400 text-center py-6">Sin tareas todavía. <a href="{{ route('projects.board', $project) }}" class="text-brand-600 hover:underline">Crear la primera</a></div>
            @else
                <div class="space-y-2">
                    @foreach($tasksByStatus as $colName => $count)
                        @php $total = $tasksByStatus->sum() ?: 1; $pct = round(($count/$total)*100); @endphp
                        <div>
                            <div class="flex justify-between text-xs mb-1"><span class="text-slate-600">{{ $colName }}</span><span class="font-semibold">{{ $count }} <span class="text-slate-400">({{ $pct }}%)</span></span></div>
                            <div class="w-full bg-slate-100 rounded-full h-2"><div class="h-2 bg-brand-500 rounded-full" style="width: {{ $pct }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-5">
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Miembros ({{ $project->members->count() }})</h3>
            <div class="space-y-2">
                @foreach($project->members as $m)
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-sm font-semibold">{{ strtoupper(substr($m->name, 0, 1)) }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium truncate">{{ $m->name }}</div>
                            <div class="text-xs text-slate-500 capitalize">{{ ['manager'=>'Manager','collaborator'=>'Colaborador','observer'=>'Observador'][$m->pivot->role] ?? $m->pivot->role }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if($project->request)
            <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-2">Solicitud de origen</h3>
                <a href="{{ route('project_requests.show', $project->request) }}" class="block text-sm hover:bg-slate-50 -mx-2 px-2 py-2 rounded">
                    <div class="font-mono text-xs text-brand-600">{{ $project->request->code }}</div>
                    <div class="text-slate-700 truncate">{{ $project->request->name }}</div>
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
