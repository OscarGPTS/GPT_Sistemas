@extends('layouts.app')
@section('title', 'Importar activos')
@section('page-title', 'Importar activos')
@section('page-subtitle', 'Carga masiva de equipos desde CSV')

@section('content')
<div class="bg-white rounded-xl shadow-card border border-slate-200 p-6 max-w-3xl">
    <form method="POST" action="{{ route('assets.import') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
            <div class="font-semibold mb-1.5">📋 Formato del CSV</div>
            <p class="mb-2">El archivo debe iniciar con una fila de cabeceras. Columnas aceptadas:</p>
            <code class="block bg-white text-xs p-2 rounded border border-blue-200 text-slate-700">internal_code, type, brand, model, serial_number, location, status, condition, purchase_date, purchase_cost, warranty_until</code>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-2">Archivo CSV *</label>
            <input type="file" name="file" accept=".csv,text/csv" required
                   class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
        </div>
        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
            <a href="{{ route('assets.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm">Cancelar</a>
            <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-6 py-2.5 rounded-lg font-medium text-sm shadow-sm">Importar CSV</button>
        </div>
    </form>
</div>
@endsection
