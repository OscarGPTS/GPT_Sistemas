@extends('layouts.app')
@section('title', $project->exists ? 'Editar proyecto' : 'Nuevo proyecto')
@section('page-title', $project->exists ? 'Editar proyecto' : 'Nuevo proyecto')
@section('page-subtitle', $project->exists ? $project->name : 'Configura el proyecto y miembros')

@section('content')
<form method="POST" action="{{ $project->exists ? route('projects.update', $project) : route('projects.store') }}"
      class="bg-white rounded-xl shadow-card border border-slate-200 p-6 space-y-6 max-w-5xl">
    @csrf
    @if($project->exists) @method('PUT') @endif

    @php
        $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
        $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
    @endphp

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Información general</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="{{ $label }}">Nombre del proyecto *</label>
                <input name="name" value="{{ old('name', $project->name) }}" required class="{{ $input }}">
            </div>
            <div class="md:col-span-2">
                <label class="{{ $label }}">Descripción</label>
                <textarea name="description" rows="3" class="{{ $input }}">{{ old('description', $project->description) }}</textarea>
            </div>
            <div>
                <label class="{{ $label }}">Área solicitante</label>
                <input name="area" value="{{ old('area', $project->area) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Manager *</label>
                <select name="manager_id" class="{{ $input }}">
                    <option value="">— Asignar al crear —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('manager_id', $project->manager_id) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Estado y planeación</h3>
        <div class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="{{ $label }}">Estado *</label>
                <select name="status" class="{{ $input }}">
                    @foreach(['planning'=>'Planeación','in_progress'=>'En curso','paused'=>'Pausado','completed'=>'Finalizado','cancelled'=>'Cancelado'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('status', $project->status ?? 'planning') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Prioridad *</label>
                <select name="priority" class="{{ $input }}">
                    @foreach(['low'=>'Baja','medium'=>'Media','high'=>'Alta','urgent'=>'Urgente'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('priority', $project->priority ?? 'medium') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Fecha inicio</label>
                <input type="date" name="start_date" value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Fecha fin</label>
                <input type="date" name="end_date" value="{{ old('end_date', optional($project->end_date)->format('Y-m-d')) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Presupuesto</label>
                <input type="number" step="0.01" name="budget" value="{{ old('budget', $project->budget) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Color</label>
                <input type="color" name="color" value="{{ old('color', $project->color ?? '#6366f1') }}" class="mt-1 w-full h-10 border border-slate-300 rounded-lg cursor-pointer">
            </div>
        </div>
    </div>

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Miembros del proyecto</h3>
        <p class="text-xs text-slate-500 mb-3">Selecciona el rol de cada miembro: <b>Manager</b> (control total), <b>Colaborador</b> (puede gestionar tareas), <b>Observador</b> (solo lectura).</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-72 overflow-y-auto pr-2 scrollbar-thin">
            @php $existingMembers = $project->exists ? $project->members->keyBy('id') : collect(); @endphp
            @foreach($users as $u)
                @php $current = $existingMembers->get($u->id)?->pivot->role; @endphp
                <div class="flex items-center justify-between border rounded-lg px-3 py-2 {{ $current ? 'border-brand-300 bg-brand-50/40' : 'border-slate-200' }}">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-slate-800 truncate">{{ $u->name }}</div>
                            <div class="text-[11px] text-slate-500 truncate">{{ $u->department ?? '—' }}</div>
                        </div>
                    </div>
                    <select name="members[{{ $u->id }}]" class="text-xs border border-slate-300 rounded px-2 py-1">
                        <option value="">— Sin rol —</option>
                        <option value="manager" @selected($current === 'manager')>Manager</option>
                        <option value="collaborator" @selected($current === 'collaborator')>Colaborador</option>
                        <option value="observer" @selected($current === 'observer')>Observador</option>
                    </select>
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <a href="{{ route('projects.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm">Cancelar</a>
        <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-6 py-2.5 rounded-lg font-medium text-sm shadow-sm">
            {{ $project->exists ? 'Actualizar proyecto' : 'Crear proyecto' }}
        </button>
    </div>
</form>
@endsection
