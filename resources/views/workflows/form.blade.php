@extends('layouts.app')
@section('title', $workflow->exists ? 'Editar flujo' : 'Nuevo flujo')
@section('page-title', $workflow->exists ? 'Editar flujo' : 'Nuevo flujo')
@section('page-subtitle', 'Configura los pasos de aprobación dinámicamente')

@section('content')
<form method="POST" action="{{ $workflow->exists ? route('workflows.update', $workflow) : route('workflows.store') }}"
      class="bg-white rounded-xl shadow-card border border-slate-200 p-6 space-y-6 max-w-5xl"
      x-data="{ steps: @js(old('steps', $workflow->steps->map(fn($s) => [
            'name' => $s->name,
            'approver_type' => $s->approver_type,
            'role_id' => $s->role_id,
            'user_id' => $s->user_id,
            'is_optional' => $s->is_optional,
            'allow_parallel' => $s->allow_parallel,
            'instructions' => $s->instructions,
      ])->values()->all()) }">
    @csrf
    @if($workflow->exists) @method('PUT') @endif

    @php
        $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
        $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
        $inputSm = 'mt-1 w-full border border-slate-300 rounded-md px-2.5 py-1.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
    @endphp

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Configuración general</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="{{ $label }}">Nombre *</label>
                <input name="name" value="{{ old('name', $workflow->name) }}" required class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Slug</label>
                <input name="slug" value="{{ old('slug', $workflow->slug) }}" class="{{ $input }}" placeholder="auto-generado si se deja vacío">
            </div>
            <div>
                <label class="{{ $label }}">Tipo de solicitud *</label>
                <input name="target_type" value="{{ old('target_type', $workflow->target_type ?? 'equipment_request') }}" required class="{{ $input }}"
                       placeholder="equipment_request, ticket, ...">
            </div>
            <div>
                <label class="{{ $label }}">Modo *</label>
                <select name="mode" class="{{ $input }}">
                    <option value="sequential" @selected(old('mode', $workflow->mode) === 'sequential')>Secuencial (paso por paso)</option>
                    <option value="parallel" @selected(old('mode', $workflow->mode) === 'parallel')>Paralelo (todos a la vez)</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="{{ $label }}">Descripción</label>
                <textarea name="description" rows="2" class="{{ $input }}">{{ old('description', $workflow->description) }}</textarea>
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $workflow->is_active ?? true)) class="mr-2 rounded text-brand-600">
                    <span class="text-sm text-slate-700">Flujo activo</span>
                </label>
            </div>
        </div>
    </div>

    <div>
        <div class="flex justify-between items-center pb-2 border-b border-slate-100 mb-4">
            <h3 class="font-semibold text-slate-900">Pasos del flujo</h3>
            <button type="button"
                    @click="steps.push({name:'', approver_type:'role', role_id:null, user_id:null, is_optional:false, allow_parallel:false, instructions:''})"
                    class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white text-sm px-3 py-1.5 rounded-lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Agregar paso
            </button>
        </div>
        <div class="space-y-3">
            <template x-for="(step, i) in steps" :key="i">
                <div class="border border-slate-200 rounded-lg p-4 bg-slate-50">
                    <div class="flex justify-between items-center mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-xs font-bold" x-text="i+1"></div>
                            <span class="text-sm font-semibold text-slate-700">Paso <span x-text="i+1"></span></span>
                        </div>
                        <button type="button" @click="steps.splice(i,1)" class="text-xs text-red-600 hover:text-red-700 font-medium">Eliminar paso</button>
                    </div>
                    <div class="grid md:grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-medium text-slate-600">Nombre</label>
                            <input :name="`steps[${i}][name]`" x-model="step.name" required class="{{ $inputSm }}">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-600">Tipo de aprobador</label>
                            <select :name="`steps[${i}][approver_type]`" x-model="step.approver_type" class="{{ $inputSm }}">
                                <option value="role">Rol</option>
                                <option value="user">Usuario específico</option>
                                <option value="manager">Jefe directo del solicitante</option>
                            </select>
                        </div>
                        <div x-show="step.approver_type === 'role'">
                            <label class="text-xs font-medium text-slate-600">Rol</label>
                            <select :name="`steps[${i}][role_id]`" x-model="step.role_id" class="{{ $inputSm }}">
                                <option value="">—</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="step.approver_type === 'user'">
                            <label class="text-xs font-medium text-slate-600">Usuario</label>
                            <select :name="`steps[${i}][user_id]`" x-model="step.user_id" class="{{ $inputSm }}">
                                <option value="">—</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="text-xs font-medium text-slate-600">Instrucciones para el aprobador</label>
                            <input :name="`steps[${i}][instructions]`" x-model="step.instructions" class="{{ $inputSm }}">
                        </div>
                        <div class="md:col-span-3 flex gap-5">
                            <label class="text-xs flex items-center text-slate-600">
                                <input type="checkbox" :name="`steps[${i}][is_optional]`" value="1" x-model="step.is_optional" class="mr-1.5 rounded text-brand-600">
                                Opcional
                            </label>
                            <label class="text-xs flex items-center text-slate-600">
                                <input type="checkbox" :name="`steps[${i}][allow_parallel]`" value="1" x-model="step.allow_parallel" class="mr-1.5 rounded text-brand-600">
                                Permite paralelo
                            </label>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="steps.length === 0" class="text-sm text-slate-500 border border-dashed border-slate-300 rounded-lg p-8 text-center bg-slate-50">
                <div class="mb-2">No hay pasos configurados</div>
                <div class="text-xs">Haz click en <b>Agregar paso</b> para comenzar</div>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
        <a href="{{ route('workflows.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm">Cancelar</a>
        <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-6 py-2.5 rounded-lg font-medium text-sm shadow-sm">
            {{ $workflow->exists ? 'Actualizar flujo' : 'Crear flujo' }}
        </button>
    </div>
</form>
@endsection
