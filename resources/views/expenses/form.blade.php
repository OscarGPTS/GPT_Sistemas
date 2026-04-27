@extends('layouts.app')
@section('title', $expense->exists ? 'Editar gasto' : 'Nuevo gasto')
@section('page-title', $expense->exists ? 'Editar gasto' : 'Registrar gasto')
@section('page-subtitle', $expense->exists ? $expense->code : 'Captura un gasto recurrente o variable')

@section('content')
<form method="POST" action="{{ $expense->exists ? route('expenses.update', $expense) : route('expenses.store') }}"
      enctype="multipart/form-data"
      class="bg-white rounded-xl shadow-card border border-slate-200 p-6 space-y-5 max-w-4xl">
    @csrf
    @if($expense->exists) @method('PUT') @endif
    @php
        $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
        $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
    @endphp

    <div class="grid md:grid-cols-3 gap-4">
        <div>
            <label class="{{ $label }}">Categoría *</label>
            <select name="category_id" required class="{{ $input }}">
                <option value="">— Selecciona —</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('category_id', $expense->category_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $label }}">Tipo *</label>
            <select name="type" class="{{ $input }}">
                <option value="recurring" @selected(old('type', $expense->type) === 'recurring')>Recurrente</option>
                <option value="variable" @selected(old('type', $expense->type ?? 'variable') === 'variable')>Variable</option>
            </select>
        </div>
        <div>
            <label class="{{ $label }}">Fecha *</label>
            <input type="date" name="expense_date" value="{{ old('expense_date', optional($expense->expense_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required class="{{ $input }}">
        </div>
        <div class="md:col-span-3">
            <label class="{{ $label }}">Concepto *</label>
            <input name="concept" value="{{ old('concept', $expense->concept) }}" required class="{{ $input }}" placeholder="Ej. Renovación licencia M365 - mes abril">
        </div>
        <div>
            <label class="{{ $label }}">Monto *</label>
            <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $expense->amount) }}" required class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Proveedor</label>
            <input name="supplier" value="{{ old('supplier', $expense->supplier) }}" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">No. Factura</label>
            <input name="invoice_number" value="{{ old('invoice_number', $expense->invoice_number) }}" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Método de pago *</label>
            <select name="payment_method" class="{{ $input }}">
                @foreach(['transfer'=>'Transferencia','card'=>'Tarjeta','cash'=>'Efectivo','check'=>'Cheque','other'=>'Otro'] as $k=>$v)
                    <option value="{{ $k }}" @selected(old('payment_method', $expense->payment_method ?? 'transfer') === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="{{ $label }}">Notas</label>
            <textarea name="notes" rows="2" class="{{ $input }}">{{ old('notes', $expense->notes) }}</textarea>
        </div>
        <div class="md:col-span-3">
            <h4 class="text-xs font-semibold text-slate-500 uppercase mb-2 mt-2">Vinculaciones (opcional)</h4>
            <div class="grid md:grid-cols-3 gap-3">
                <div>
                    <label class="{{ $label }}">Proyecto</label>
                    <select name="project_id" class="{{ $input }}">
                        <option value="">—</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" @selected(old('project_id', $expense->project_id) == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}">Activo</label>
                    <select name="asset_id" class="{{ $input }}">
                        <option value="">—</option>
                        @foreach($assets as $a)
                            <option value="{{ $a->id }}" @selected(old('asset_id', $expense->asset_id) == $a->id)>{{ $a->internal_code }} · {{ $a->brand }} {{ $a->model }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}">Solicitud de equipo</label>
                    <select name="equipment_request_id" class="{{ $input }}">
                        <option value="">—</option>
                        @foreach($equipmentRequests as $r)
                            <option value="{{ $r->id }}" @selected(old('equipment_request_id', $expense->equipment_request_id) == $r->id)>{{ $r->code }} · {{ Str::limit($r->title, 40) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="md:col-span-3">
            <label class="{{ $label }}">Comprobantes</label>
            <input type="file" name="receipts[]" multiple class="mt-1 block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <a href="{{ route('expenses.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm">Cancelar</a>
        <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium shadow-sm">
            {{ $expense->exists ? 'Actualizar' : 'Registrar gasto' }}
        </button>
    </div>
</form>
@endsection
