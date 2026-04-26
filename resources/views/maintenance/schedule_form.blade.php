@extends('layouts.app')
@section('title', 'Programación de mantenimiento')
@section('page-title', 'Nueva programación')
@section('page-subtitle', 'Programación periódica de mantenimiento')

@section('content')
<form method="POST" action="{{ route('maintenance.schedules.store') }}" class="bg-white rounded-xl shadow-card border border-slate-200 p-6 space-y-5 max-w-4xl">
    @csrf
    @php
        $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
        $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
    @endphp
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="{{ $label }}">Activo *</label>
            <select name="asset_id" required class="{{ $input }}">
                @foreach($assets as $a)
                    <option value="{{ $a->id }}">{{ $a->internal_code }} · {{ $a->brand }} {{ $a->model }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $label }}">Tipo *</label>
            <select name="type" class="{{ $input }}">
                <option value="preventive">Preventivo</option>
                <option value="corrective">Correctivo</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}">Título *</label>
            <input name="title" class="{{ $input }}" required>
        </div>
        <div>
            <label class="{{ $label }}">Frecuencia *</label>
            <select name="frequency" class="{{ $input }}">
                <option value="monthly">Mensual</option>
                <option value="quarterly">Trimestral</option>
                <option value="biannual">Semestral</option>
                <option value="annual" selected>Anual</option>
                <option value="custom">Personalizado (días)</option>
            </select>
        </div>
        <div>
            <label class="{{ $label }}">Días (solo custom)</label>
            <input type="number" name="interval_days" class="{{ $input }}" min="1">
        </div>
        <div>
            <label class="{{ $label }}">Próxima ejecución *</label>
            <input type="date" name="next_due_at" value="{{ now()->addMonth()->format('Y-m-d') }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Responsable</label>
            <select name="responsible_id" class="{{ $input }}">
                <option value="">—</option>
                @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}">Descripción</label>
            <textarea name="description" rows="3" class="{{ $input }}"></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" checked class="mr-2 rounded text-brand-600">
                <span class="text-sm text-slate-700">Programación activa</span>
            </label>
        </div>
    </div>
    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <a href="{{ route('maintenance.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm">Cancelar</a>
        <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-6 py-2.5 rounded-lg font-medium text-sm shadow-sm">Crear programación</button>
    </div>
</form>
@endsection
