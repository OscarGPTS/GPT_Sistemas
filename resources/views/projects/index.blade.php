@extends('layouts.app')
@section('title', 'Proyectos')
@section('page-title', 'Proyectos')
@section('page-subtitle', 'Gestión de proyectos y tableros Kanban')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="text-sm text-slate-500"><span class="font-semibold text-slate-800">{{ $projects->total() }}</span> proyecto(s)</div>
    <div class="flex gap-2">
        <a href="{{ route('project_requests.index') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-3.5 py-2 rounded-lg text-sm font-medium">📋 Solicitudes</a>
        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('projects.create'))
        <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nuevo proyecto
        </a>
        @endif
    </div>
</div>

<form method="GET" class="bg-white rounded-xl shadow-card border border-slate-200 p-4 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar por nombre o código..." class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg text-sm">
        </div>
        <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Todos los estados</option>
            @foreach(['planning'=>'Planeación','in_progress'=>'En curso','paused'=>'Pausado','completed'=>'Finalizado','cancelled'=>'Cancelado'] as $k=>$v)
                <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <button class="bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-medium">Filtrar</button>
    </div>
</form>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($projects as $p)
        @php
            $statusCls = match($p->status) {
                'planning' => 'bg-slate-100 text-slate-600',
                'in_progress' => 'bg-blue-50 text-blue-700',
                'paused' => 'bg-amber-50 text-amber-700',
                'completed' => 'bg-emerald-50 text-emerald-700',
                'cancelled' => 'bg-red-50 text-red-700',
                default => 'bg-slate-100',
            };
            $statusLabel = ['planning'=>'Planeación','in_progress'=>'En curso','paused'=>'Pausado','completed'=>'Finalizado','cancelled'=>'Cancelado'][$p->status] ?? $p->status;
        @endphp
        <a href="{{ route('projects.board', $p) }}" class="group bg-white rounded-xl shadow-card border border-slate-200 hover:shadow-md hover:-translate-y-0.5 transition overflow-hidden">
            <div class="h-1.5" style="background: {{ $p->color }}"></div>
            <div class="p-5">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-semibold flex-shrink-0" style="background: {{ $p->color }}">
                            {{ strtoupper(substr($p->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-mono text-[11px] text-slate-400">{{ $p->code }}</div>
                            <div class="font-semibold text-slate-900 truncate">{{ $p->name }}</div>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $statusCls }} flex-shrink-0">{{ $statusLabel }}</span>
                </div>
                @if($p->description)
                    <p class="text-sm text-slate-500 mb-3 line-clamp-2">{{ Str::limit($p->description, 100) }}</p>
                @endif
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75"/></svg>{{ $p->tasks_count }} tareas</span>
                        @if($p->end_date)
                            <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5"/></svg>{{ $p->end_date->format('d/m/Y') }}</span>
                        @endif
                    </div>
                    <div class="flex -space-x-1.5">
                        @foreach($p->members->take(4) as $m)
                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-[10px] font-bold border-2 border-white" title="{{ $m->name }}">{{ strtoupper(substr($m->name, 0, 1)) }}</div>
                        @endforeach
                        @if($p->members->count() > 4)
                            <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[10px] font-bold border-2 border-white">+{{ $p->members->count() - 4 }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </a>
    @empty
        <div class="col-span-full bg-white rounded-xl shadow-card border border-slate-200 p-12 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
            <div class="text-sm font-medium text-slate-600">Sin proyectos</div>
            <div class="text-xs text-slate-500 mt-1">Crea tu primer proyecto o solicita uno nuevo</div>
        </div>
    @endforelse
</div>

@if($projects->hasPages())<div class="mt-5">{{ $projects->links() }}</div>@endif
@endsection
