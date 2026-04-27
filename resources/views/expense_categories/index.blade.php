@extends('layouts.app')
@section('title', 'Categorías de gasto')
@section('page-title', 'Categorías de gasto')
@section('page-subtitle', 'Configura categorías y presupuestos mensuales')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-900">Categorías existentes</h3>
        </div>
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Categoría</th>
                    <th class="px-5 py-3">Presupuesto</th>
                    <th class="px-5 py-3">Gastos</th>
                    <th class="px-5 py-3">Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($categories as $cat)
                    <tr>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full" style="background: {{ $cat->color }}"></span>
                                <div>
                                    <div class="font-medium text-slate-800">{{ $cat->name }}</div>
                                    @if($cat->description)<div class="text-xs text-slate-500">{{ $cat->description }}</div>@endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 font-mono text-sm">{{ $cat->monthly_budget ? '$ '.number_format((float) $cat->monthly_budget, 2) : '—' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $cat->expenses_count }}</td>
                        <td class="px-5 py-3">
                            @if($cat->is_active)
                                <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">Activa</span>
                            @else
                                <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">Inactiva</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button onclick="document.getElementById('edit-{{ $cat->id }}').classList.toggle('hidden')" class="text-brand-600 hover:underline text-xs">Editar</button>
                            @if($cat->expenses_count === 0)
                                <form method="POST" action="{{ route('expense_categories.destroy', $cat) }}" class="inline ml-2" onsubmit="return confirm('¿Eliminar?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:underline text-xs">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    <tr id="edit-{{ $cat->id }}" class="hidden bg-slate-50">
                        <td colspan="5" class="px-5 py-3">
                            <form method="POST" action="{{ route('expense_categories.update', $cat) }}" class="grid md:grid-cols-5 gap-2 items-end">
                                @csrf @method('PUT')
                                <div class="md:col-span-2">
                                    <label class="text-xs text-slate-500">Nombre</label>
                                    <input name="name" value="{{ $cat->name }}" required class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-500">Color</label>
                                    <input type="color" name="color" value="{{ $cat->color }}" class="w-full h-8 rounded">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-500">Presupuesto mensual</label>
                                    <input type="number" step="0.01" name="monthly_budget" value="{{ $cat->monthly_budget }}" class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs">
                                        <input type="checkbox" name="is_active" value="1" @checked($cat->is_active)> Activa
                                    </label>
                                    <button class="bg-brand-600 hover:bg-brand-700 text-white px-3 py-1 rounded text-xs">Guardar</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
        <h3 class="font-semibold text-slate-900 mb-3">Nueva categoría</h3>
        <form method="POST" action="{{ route('expense_categories.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-semibold text-slate-700 uppercase">Nombre *</label>
                <input name="name" required class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-700 uppercase">Color</label>
                <input type="color" name="color" value="#6366f1" class="mt-1 w-full h-10 border border-slate-300 rounded-lg cursor-pointer">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-700 uppercase">Presupuesto mensual</label>
                <input type="number" step="0.01" name="monthly_budget" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-700 uppercase">Descripción</label>
                <textarea name="description" rows="2" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <label class="inline-flex items-center text-sm">
                <input type="checkbox" name="is_active" value="1" checked class="mr-2 rounded text-brand-600">
                Categoría activa
            </label>
            <button class="w-full bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 text-white py-2 rounded-lg text-sm font-medium">+ Crear categoría</button>
        </form>
    </div>
</div>
@endsection
