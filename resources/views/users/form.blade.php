@extends('layouts.app')
@section('title', $user->exists ? 'Editar usuario' : 'Nuevo usuario')
@section('page-title', $user->exists ? 'Editar usuario' : 'Nuevo usuario')
@section('page-subtitle', $user->exists ? $user->name : 'Alta de colaborador')

@section('content')
<form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}"
      class="bg-white rounded-xl shadow-card border border-slate-200 p-6 space-y-5 max-w-4xl">
    @csrf
    @if($user->exists) @method('PUT') @endif
    @php
        $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
        $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
    @endphp

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="{{ $label }}">Nombre *</label>
            <input name="name" value="{{ old('name', $user->name) }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Email *</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Código de empleado</label>
            <input name="employee_code" value="{{ old('employee_code', $user->employee_code) }}" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Teléfono</label>
            <input name="phone" value="{{ old('phone', $user->phone) }}" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Área / Departamento</label>
            <input name="department" value="{{ old('department', $user->department) }}" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Puesto</label>
            <input name="position" value="{{ old('position', $user->position) }}" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Jefe directo</label>
            <select name="manager_id" class="{{ $input }}">
                <option value="">—</option>
                @foreach($managers as $m)
                    <option value="{{ $m->id }}" @selected(old('manager_id', $user->manager_id) == $m->id)>{{ $m->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $label }}">Contraseña {{ $user->exists ? '(dejar vacío para no cambiar)' : '*' }}</label>
            <input type="password" name="password" class="{{ $input }}">
        </div>
    </div>

    <div>
        <label class="{{ $label }} mb-2">Roles asignados</label>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
            @foreach($roles as $r)
                @php $checked = in_array($r->id, old('roles', $user->roles->pluck('id')->all())); @endphp
                <label class="flex items-center gap-2 border rounded-lg px-3 py-2.5 text-sm cursor-pointer transition
                              {{ $checked ? 'border-brand-500 bg-brand-50 text-brand-800' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                    <input type="checkbox" name="roles[]" value="{{ $r->id }}" @checked($checked) class="rounded text-brand-600 focus:ring-brand-500">
                    <span>{{ $r->label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <label class="inline-flex items-center">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="mr-2 rounded text-brand-600 focus:ring-brand-500">
            <span class="text-sm text-slate-700">Usuario activo</span>
        </label>
    </div>

    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <a href="{{ route('users.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm">Cancelar</a>
        <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-6 py-2.5 rounded-lg font-medium text-sm shadow-sm">
            {{ $user->exists ? 'Actualizar' : 'Crear usuario' }}
        </button>
    </div>
</form>
@endsection
