@extends('layouts.app')
@section('title', 'Nueva asignación')
@section('page-title', 'Nueva asignación')
@section('page-subtitle', 'Entregar un activo a un colaborador')

@section('content')
<form method="POST" action="{{ route('assignments.store') }}" class="bg-white rounded-xl shadow-card border border-slate-200 p-6 space-y-5 max-w-3xl">
    @csrf
    @php
        $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
        $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
    @endphp

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="{{ $label }}">Activo *</label>
            <select name="asset_id" required class="{{ $input }}">
                <option value="">—</option>
                @if($asset)
                    <option value="{{ $asset->id }}" selected>{{ $asset->internal_code }} · {{ $asset->brand }} {{ $asset->model }}</option>
                @endif
                @foreach($availableAssets as $a)
                    @continue($asset && $a->id === $asset->id)
                    <option value="{{ $a->id }}">{{ $a->internal_code }} · {{ $a->brand }} {{ $a->model }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $label }}">Usuario *</label>
            <select name="user_id" required class="{{ $input }}">
                <option value="">—</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->department ?? '—' }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $label }}">Ubicación</label>
            <input name="assignment_location" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Condición de entrega</label>
            <input name="condition_out" class="{{ $input }}" placeholder="Completo, sin daños...">
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}">Motivo / Observaciones</label>
            <textarea name="assignment_reason" rows="3" class="{{ $input }}"></textarea>
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <a href="{{ route('assignments.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm">Cancelar</a>
        <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-6 py-2.5 rounded-lg font-medium text-sm shadow-sm">Asignar activo</button>
    </div>
</form>
@endsection
