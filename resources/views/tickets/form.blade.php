@extends('layouts.app')
@section('title', 'Nuevo ticket')
@section('page-title', 'Nuevo ticket')
@section('page-subtitle', 'Reporta una incidencia, solicitud o mantenimiento')

@section('content')
<form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data"
      class="bg-white rounded-xl shadow-card border border-slate-200 p-6 space-y-5 max-w-4xl">
    @csrf
    @php
        $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition';
        $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
    @endphp

    <div class="grid md:grid-cols-3 gap-4">
        <div>
            <label class="{{ $label }}">Tipo *</label>
            <select name="type" class="{{ $input }}">
                <option value="incident">Incidente</option>
                <option value="request">Solicitud</option>
                <option value="maintenance">Mantenimiento</option>
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
            <label class="{{ $label }}">Activo relacionado</label>
            <select name="asset_id" class="{{ $input }}">
                <option value="">— Ninguno —</option>
                @foreach($assets as $a)
                    <option value="{{ $a->id }}">{{ $a->internal_code }} · {{ $a->brand }} {{ $a->model }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="{{ $label }}">Asunto *</label>
        <input name="subject" class="{{ $input }}" required placeholder="Descripción breve del tema...">
    </div>
    <div>
        <label class="{{ $label }}">Descripción *</label>
        <textarea name="description" rows="5" class="{{ $input }}" required placeholder="Describe con detalle lo que sucede..."></textarea>
    </div>
    <div>
        <label class="{{ $label }}">Adjuntos (opcional)</label>
        <input type="file" name="attachments[]" multiple class="mt-1 block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
    </div>

    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <a href="{{ route('tickets.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm">Cancelar</a>
        <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-6 py-2.5 rounded-lg font-medium text-sm shadow-sm">
            Crear ticket
        </button>
    </div>
</form>
@endsection
