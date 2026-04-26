@extends('layouts.app')
@section('title', $asset->exists ? 'Editar activo' : 'Nuevo activo')
@section('page-title', $asset->exists ? 'Editar activo' : 'Nuevo activo')
@section('page-subtitle', $asset->exists ? $asset->internal_code : 'Alta en el inventario')

@section('content')
<form method="POST" action="{{ $asset->exists ? route('assets.update', $asset) : route('assets.store') }}"
      class="bg-white rounded-xl shadow-card border border-slate-200 p-6 space-y-6 max-w-5xl">
    @csrf
    @if($asset->exists) @method('PUT') @endif

    @php
        $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition';
        $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
    @endphp

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Identificación</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="{{ $label }}">Código interno *</label>
                <input name="internal_code" value="{{ old('internal_code', $asset->internal_code) }}" required class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Categoría</label>
                <select name="category_id" class="{{ $input }}">
                    <option value="">—</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id', $asset->category_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Tipo *</label>
                <input name="type" value="{{ old('type', $asset->type) }}" required class="{{ $input }}" placeholder="laptop, desktop, monitor...">
            </div>
        </div>
    </div>

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Equipo</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="{{ $label }}">Marca</label>
                <input name="brand" value="{{ old('brand', $asset->brand) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Modelo</label>
                <input name="model" value="{{ old('model', $asset->model) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Número de serie</label>
                <input name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="{{ $input }}">
            </div>
        </div>
    </div>

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Estado y ubicación</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="{{ $label }}">Estado *</label>
                <select name="status" class="{{ $input }}">
                    @foreach(['available'=>'Disponible','assigned'=>'Asignado','in_maintenance'=>'En mantenimiento','retired'=>'Baja','lost'=>'Extraviado'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('status', $asset->status ?? 'available') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Condición *</label>
                <select name="condition" class="{{ $input }}">
                    @foreach(['new'=>'Nuevo','good'=>'Bueno','fair'=>'Regular','poor'=>'Pobre'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('condition', $asset->condition ?? 'good') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Ubicación</label>
                <input name="location" value="{{ old('location', $asset->location) }}" class="{{ $input }}">
            </div>
        </div>
    </div>

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Compra y garantía</h3>
        <div class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="{{ $label }}">Fecha de compra</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($asset->purchase_date)->format('Y-m-d')) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Costo</label>
                <input type="number" step="0.01" name="purchase_cost" value="{{ old('purchase_cost', $asset->purchase_cost) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Proveedor</label>
                <input name="supplier" value="{{ old('supplier', $asset->supplier) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Garantía hasta</label>
                <input type="date" name="warranty_until" value="{{ old('warranty_until', optional($asset->warranty_until)->format('Y-m-d')) }}" class="{{ $input }}">
            </div>
        </div>
    </div>

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Información adicional</h3>
        <div class="grid gap-4">
            <div>
                <label class="{{ $label }}">Descripción</label>
                <input name="description" value="{{ old('description', $asset->description) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Notas</label>
                <textarea name="notes" rows="3" class="{{ $input }}">{{ old('notes', $asset->notes) }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-2">
        <a href="{{ route('assets.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm">Cancelar</a>
        <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-6 py-2.5 rounded-lg font-medium text-sm shadow-sm">
            {{ $asset->exists ? 'Actualizar' : 'Crear activo' }}
        </button>
    </div>
</form>
@endsection
