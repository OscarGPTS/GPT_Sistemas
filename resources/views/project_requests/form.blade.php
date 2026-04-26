@extends('layouts.app')
@section('title', 'Nueva solicitud de proyecto')
@section('page-title', 'Nueva solicitud de proyecto')
@section('page-subtitle', 'Llena el formulario y envía a aprobación')

@section('content')
<form method="POST" action="{{ route('project_requests.store') }}" class="bg-white rounded-xl shadow-card border border-slate-200 p-6 space-y-5 max-w-4xl">
    @csrf
    @php
        $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
        $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
    @endphp

    <div class="grid md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="{{ $label }}">Nombre del proyecto *</label>
            <input name="name" required class="{{ $input }}" placeholder="Ej. Migración del CRM a la nueva plataforma">
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}">Descripción *</label>
            <textarea name="description" rows="4" required class="{{ $input }}" placeholder="Describe el alcance, objetivos y entregables esperados."></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}">Justificación / impacto esperado</label>
            <textarea name="justification" rows="3" class="{{ $input }}" placeholder="¿Por qué es necesario este proyecto? ¿Qué problema resuelve o qué oportunidad genera?"></textarea>
        </div>
        <div>
            <label class="{{ $label }}">Impacto *</label>
            <select name="impact" class="{{ $input }}">
                <option value="low">Bajo</option>
                <option value="medium" selected>Medio</option>
                <option value="high">Alto</option>
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
            <label class="{{ $label }}">Presupuesto estimado</label>
            <input type="number" step="0.01" name="budget_estimate" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Área esperada</label>
            <input name="expected_area" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Inicio deseado</label>
            <input type="date" name="desired_start_date" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Fin deseado</label>
            <input type="date" name="desired_end_date" class="{{ $input }}">
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <a href="{{ route('project_requests.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm">Cancelar</a>
        <button name="action" value="draft" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-5 py-2.5 rounded-lg text-sm font-medium">Guardar borrador</button>
        <button name="action" value="submit" class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium shadow-sm">
            Enviar a aprobación
        </button>
    </div>
</form>
@endsection
