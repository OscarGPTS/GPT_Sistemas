@extends('layouts.app')
@section('title', $location->exists ? 'Editar ubicación' : 'Nueva ubicación')
@section('page-title', $location->exists ? 'Editar ubicación' : 'Nueva ubicación')
@section('page-subtitle', $location->exists ? $location->fullName() : 'Define un nuevo nodo en la estructura')

@section('content')
<form method="POST" action="{{ $location->exists ? route('locations.update', $location) : route('locations.store') }}"
      class="bg-white rounded-xl shadow-card border border-slate-200 p-6 space-y-5 max-w-3xl">
    @csrf
    @if($location->exists) @method('PUT') @endif
    @php
        $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
        $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
    @endphp

    <div class="grid md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="{{ $label }}">Nombre *</label>
            <input name="name" value="{{ old('name', $location->name) }}" required class="{{ $input }}" placeholder="Ej. Piso 5, Edificio A, Almacén TI">
        </div>
        <div>
            <label class="{{ $label }}">Tipo *</label>
            <select name="type" class="{{ $input }}">
                @foreach(\App\Models\Location::TYPES as $k => $v)
                    <option value="{{ $k }}" @selected(old('type', $location->type ?? 'area') === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $label }}">Ubicación padre</label>
            <select name="parent_id" class="{{ $input }}">
                <option value="">— Raíz (sin padre) —</option>
                @foreach($flatList as $loc)
                    @continue($location->exists && $loc['id'] === $location->id)
                    <option value="{{ $loc['id'] }}" @selected(old('parent_id', $location->parent_id) == $loc['id'])>
                        {{ $loc['indented'] }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}">Dirección</label>
            <input name="address" value="{{ old('address', $location->address) }}" class="{{ $input }}">
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}">Descripción</label>
            <textarea name="description" rows="2" class="{{ $input }}">{{ old('description', $location->description) }}</textarea>
        </div>
        <div>
            <label class="{{ $label }}">Posición (orden)</label>
            <input type="number" name="position" value="{{ old('position', $location->position ?? 0) }}" min="0" class="{{ $input }}">
        </div>
        <div class="flex items-end">
            <label class="inline-flex items-center text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $location->is_active ?? true)) class="mr-2 rounded text-brand-600">
                Ubicación activa
            </label>
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <a href="{{ route('locations.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm">Cancelar</a>
        <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium shadow-sm">
            {{ $location->exists ? 'Actualizar' : 'Crear ubicación' }}
        </button>
    </div>
</form>
@endsection
