@extends('layouts.app')
@section('title', $deviceUser->exists ? 'Editar usuario de impresión' : 'Nuevo usuario de impresión')
@section('page-title', $deviceUser->exists ? 'Editar usuario de impresión' : 'Nuevo usuario de impresión')
@section('page-subtitle', $deviceUser->exists ? $deviceUser->full_name : 'Asigna código de impresión y ubicación')

@section('content')
<form method="POST" action="{{ $deviceUser->exists ? route('device_users.update', $deviceUser) : route('device_users.store') }}"
      class="bg-white rounded-xl shadow-card border border-slate-200 p-6 space-y-5 max-w-4xl">
    @csrf
    @if($deviceUser->exists) @method('PUT') @endif
    @php
        $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
        $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
    @endphp

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Identificación</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="{{ $label }}">Nombre completo *</label>
                <input name="full_name" value="{{ old('full_name', $deviceUser->full_name) }}" required class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Código de empleado</label>
                <input name="employee_code" value="{{ old('employee_code', $deviceUser->employee_code) }}" class="{{ $input }}" placeholder="Ej. EMP-001">
            </div>
            <div>
                <label class="{{ $label }}">Correo electrónico</label>
                <input type="email" name="email" value="{{ old('email', $deviceUser->email) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Buzón</label>
                <input name="mailbox" value="{{ old('mailbox', $deviceUser->mailbox) }}" class="{{ $input }}" placeholder="Ej. INBOX-005">
            </div>
            <div class="md:col-span-2">
                <label class="{{ $label }}">Vincular a usuario del sistema (opcional)</label>
                <select name="user_id" class="{{ $input }}">
                    <option value="">— Sin vincular —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('user_id', $deviceUser->user_id) == $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 mt-1">Si vinculas a un usuario del sistema, esa persona podrá ver su propio código en "Mi código de impresión".</p>
            </div>
        </div>
    </div>

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Código y ubicación</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="{{ $label }}">Código de impresión (PIN) *</label>
                <input name="print_code" value="{{ old('print_code', $deviceUser->print_code) }}" required class="{{ $input }} font-mono" placeholder="Ej. 12345">
                <p class="text-xs text-slate-500 mt-1">Único en el sistema. Usado en la impresora para identificar al usuario.</p>
            </div>
            <div>
                <label class="{{ $label }}">Ubicación</label>
                <select name="location_id" class="{{ $input }}">
                    <option value="">— Sin ubicación —</option>
                    @foreach($flatLocations as $loc)
                        <option value="{{ $loc['id'] }}" @selected(old('location_id', $deviceUser->location_id) == $loc['id'])>
                            {{ $loc['indented'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($printers->isNotEmpty())
    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Impresoras autorizadas (opcional)</h3>
        <p class="text-xs text-slate-500 mb-3">Selecciona las impresoras donde este usuario puede imprimir. Si no seleccionas ninguna, podrá imprimir en cualquiera (acceso abierto).</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            @php $current = $deviceUser->exists ? $deviceUser->printers->pluck('id')->all() : []; @endphp
            @foreach($printers as $p)
                @php $checked = in_array($p->id, old('printers', $current)); @endphp
                <label class="flex items-center gap-2 border rounded-lg px-3 py-2.5 cursor-pointer
                              {{ $checked ? 'border-brand-500 bg-brand-50/40' : 'border-slate-200 hover:border-slate-300' }}">
                    <input type="checkbox" name="printers[]" value="{{ $p->id }}" @checked($checked) class="rounded text-brand-600">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-slate-800 truncate">{{ $p->brand }} {{ $p->model }}</div>
                        <div class="text-xs text-slate-500 font-mono">{{ $p->internal_code }}</div>
                    </div>
                </label>
            @endforeach
        </div>
    </div>
    @endif

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Adicional</h3>
        <div class="space-y-3">
            <div>
                <label class="{{ $label }}">Notas</label>
                <textarea name="notes" rows="2" class="{{ $input }}">{{ old('notes', $deviceUser->notes) }}</textarea>
            </div>
            <label class="inline-flex items-center text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $deviceUser->is_active ?? true)) class="mr-2 rounded text-brand-600">
                Usuario activo
            </label>
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <a href="{{ route('device_users.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm">Cancelar</a>
        <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium shadow-sm">
            {{ $deviceUser->exists ? 'Actualizar' : 'Crear usuario' }}
        </button>
    </div>
</form>
@endsection
