@extends('layouts.app')
@section('title', 'Nueva solicitud de equipo')
@section('page-title', 'Nueva solicitud de equipo')
@section('page-subtitle', 'Solicita el equipo que necesitas para tu trabajo')

@section('content')
<form method="POST" action="{{ route('equipment_requests.store') }}"
      class="bg-white rounded-xl shadow-card border border-slate-200 p-6 space-y-6 max-w-5xl"
      x-data="{
        items: [{type:'', description:'', suggested_model:'', purchase_link:'', quantity:1, estimated_unit_cost:'', is_inventoriable:true}],
        addItem() { this.items.push({type:'', description:'', suggested_model:'', purchase_link:'', quantity:1, estimated_unit_cost:'', is_inventoriable:true}); },
        removeItem(i) { this.items.splice(i, 1); },
        get total() { return this.items.reduce((s, it) => s + (parseFloat(it.estimated_unit_cost || 0) * (parseInt(it.quantity || 0))), 0); }
      }">
    @csrf

    @php
        $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
        $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
    @endphp

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Información general</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="{{ $label }}">Título de la solicitud *</label>
                <input name="title" value="{{ old('title') }}" required class="{{ $input }}" placeholder="Ej. Renovación de mouse y teclado">
            </div>
            <div class="md:col-span-2">
                <label class="{{ $label }}">Justificación</label>
                <textarea name="justification" rows="3" class="{{ $input }}" placeholder="¿Por qué necesitas este equipo? ¿Cómo se usará?">{{ old('justification') }}</textarea>
            </div>
            <div>
                <label class="{{ $label }}">Prioridad *</label>
                <select name="priority" class="{{ $input }}">
                    @foreach(['low'=>'Baja','medium'=>'Media','high'=>'Alta','urgent'=>'Urgente'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('priority','medium') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Proyecto relacionado (opcional)</label>
                <select name="project_id" class="{{ $input }}">
                    <option value="">— Ninguno —</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div>
        <div class="flex justify-between items-center pb-2 border-b border-slate-100 mb-3">
            <h3 class="font-semibold text-slate-900">Equipos solicitados</h3>
            <button type="button" @click="addItem"
                    class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white text-sm px-3 py-1.5 rounded-lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Agregar item
            </button>
        </div>
        <div class="space-y-3">
            <template x-for="(item, i) in items" :key="i">
                <div class="border border-slate-200 rounded-lg p-4 bg-slate-50">
                    <div class="flex justify-between mb-2">
                        <span class="text-xs font-bold text-slate-500">Item <span x-text="i+1"></span></span>
                        <button type="button" @click="removeItem(i)" x-show="items.length > 1" class="text-xs text-red-600 hover:text-red-700 font-medium">Quitar</button>
                    </div>
                    <div class="grid md:grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-medium text-slate-600">Tipo *</label>
                            <input :name="`items[${i}][type]`" x-model="item.type" required class="mt-1 w-full border border-slate-300 rounded-md px-2.5 py-1.5 text-sm" placeholder="mouse, teclado, RAM...">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-medium text-slate-600">Descripción *</label>
                            <input :name="`items[${i}][description]`" x-model="item.description" required class="mt-1 w-full border border-slate-300 rounded-md px-2.5 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-600">Modelo sugerido</label>
                            <input :name="`items[${i}][suggested_model]`" x-model="item.suggested_model" class="mt-1 w-full border border-slate-300 rounded-md px-2.5 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-600">Link de compra</label>
                            <input :name="`items[${i}][purchase_link]`" x-model="item.purchase_link" class="mt-1 w-full border border-slate-300 rounded-md px-2.5 py-1.5 text-sm" placeholder="https://...">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-600">Cantidad *</label>
                            <input :name="`items[${i}][quantity]`" x-model.number="item.quantity" type="number" min="1" required class="mt-1 w-full border border-slate-300 rounded-md px-2.5 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-600">Costo unitario estimado</label>
                            <input :name="`items[${i}][estimated_unit_cost]`" x-model.number="item.estimated_unit_cost" type="number" step="0.01" min="0" class="mt-1 w-full border border-slate-300 rounded-md px-2.5 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-600">Total línea</label>
                            <div class="mt-1 px-2.5 py-1.5 text-sm font-semibold text-slate-700" x-text="'$ ' + ((parseFloat(item.estimated_unit_cost||0) * (parseInt(item.quantity||0))).toFixed(2))"></div>
                        </div>
                        <div class="md:col-span-3">
                            <label class="inline-flex items-center text-sm text-slate-700">
                                <input type="checkbox" :name="`items[${i}][is_inventoriable]`" x-model="item.is_inventoriable" class="mr-2 rounded text-brand-600 focus:ring-brand-500">
                                Inventariable (al entregar se creará un activo automáticamente)
                            </label>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="mt-3 flex justify-end items-center gap-3 text-sm">
            <span class="text-slate-500">Total estimado:</span>
            <span class="text-xl font-bold text-slate-900" x-text="'$ ' + total.toFixed(2)"></span>
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <a href="{{ route('equipment_requests.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm">Cancelar</a>
        <button name="action" value="draft" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-5 py-2.5 rounded-lg text-sm font-medium">Guardar borrador</button>
        <button name="action" value="submit" class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium shadow-sm">
            Enviar a aprobación →
        </button>
    </div>
</form>
@endsection
