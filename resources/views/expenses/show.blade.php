@extends('layouts.app')
@section('title', 'Gasto '.$expense->code)
@section('page-title', $expense->code)
@section('page-subtitle', $expense->concept)

@section('content')
<div class="bg-white rounded-xl shadow-card border border-slate-200 p-5 mb-5 flex flex-wrap items-start justify-between gap-3">
    <div>
        <div class="flex items-center gap-2 flex-wrap mb-1">
            <span class="font-mono text-xs text-slate-400">{{ $expense->code }}</span>
            <span class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded-full {{ $expense->type === 'recurring' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                {{ $expense->type === 'recurring' ? 'Recurrente' : 'Variable' }}
            </span>
            @if($expense->category)
                <span class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded-full bg-slate-100">
                    <span class="w-2 h-2 rounded-full" style="background: {{ $expense->category->color }}"></span>
                    {{ $expense->category->name }}
                </span>
            @endif
        </div>
        <h1 class="text-xl font-bold text-slate-900">{{ $expense->concept }}</h1>
        <div class="text-sm text-slate-500 mt-1">{{ $expense->expense_date?->format('d/m/Y') }} · Registrado por {{ $expense->creator?->name ?? 'Sistema' }}</div>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('expenses.edit', $expense) }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-3.5 py-2 rounded-lg text-sm font-medium">Editar</a>
        <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('¿Eliminar gasto?')">
            @csrf @method('DELETE')
            <button class="bg-red-600 hover:bg-red-500 text-white px-3.5 py-2 rounded-lg text-sm font-medium">Eliminar</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <div class="text-center mb-4">
                <div class="text-xs text-slate-500 uppercase">Monto</div>
                <div class="text-4xl font-bold text-slate-900 mt-1">$ {{ number_format((float) $expense->amount, 2) }}</div>
            </div>
            <dl class="grid grid-cols-2 gap-3 text-sm pt-4 border-t border-slate-100">
                <div><dt class="text-xs text-slate-500 uppercase">Proveedor</dt><dd class="font-medium">{{ $expense->supplier ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">Factura</dt><dd class="font-mono text-sm">{{ $expense->invoice_number ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">Método de pago</dt><dd class="capitalize">{{ ['transfer'=>'Transferencia','card'=>'Tarjeta','cash'=>'Efectivo','check'=>'Cheque','other'=>'Otro'][$expense->payment_method] ?? $expense->payment_method }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">Fecha de gasto</dt><dd>{{ $expense->expense_date?->format('d/m/Y') }}</dd></div>
            </dl>
            @if($expense->notes)
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <div class="text-xs text-slate-500 uppercase mb-2">Notas</div>
                    <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $expense->notes }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Comprobantes</h3>
            @if($expense->documents->count())
                <ul class="divide-y divide-slate-100">
                    @foreach($expense->documents as $doc)
                        <li class="py-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25"/></svg>
                            <a href="{{ route('expenses.documents.download', $doc->id) }}" class="text-sm text-brand-600 hover:underline flex-1">{{ $doc->original_name }}</a>
                            <span class="text-xs text-slate-400">{{ number_format(($doc->size ?? 0)/1024, 0) }} KB</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-sm text-slate-400 text-center py-6">Sin comprobantes adjuntos.</div>
            @endif
        </div>
    </div>

    <div class="space-y-5">
        @if($expense->project || $expense->asset || $expense->equipmentRequest)
            <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">Vinculaciones</h3>
                <div class="space-y-2 text-sm">
                    @if($expense->project)
                        <a href="{{ route('projects.show', $expense->project) }}" class="block bg-blue-50 border border-blue-200 rounded-lg p-2 hover:bg-blue-100">
                            <div class="text-xs text-blue-600">📋 Proyecto</div>
                            <div class="font-medium text-slate-800">{{ $expense->project->name }}</div>
                        </a>
                    @endif
                    @if($expense->asset)
                        <a href="{{ route('assets.show', $expense->asset) }}" class="block bg-emerald-50 border border-emerald-200 rounded-lg p-2 hover:bg-emerald-100">
                            <div class="text-xs text-emerald-600">📦 Activo</div>
                            <div class="font-mono text-xs">{{ $expense->asset->internal_code }}</div>
                            <div class="text-slate-700">{{ $expense->asset->brand }} {{ $expense->asset->model }}</div>
                        </a>
                    @endif
                    @if($expense->equipmentRequest)
                        <a href="{{ route('equipment_requests.show', $expense->equipmentRequest) }}" class="block bg-violet-50 border border-violet-200 rounded-lg p-2 hover:bg-violet-100">
                            <div class="text-xs text-violet-600">🛒 Solicitud de equipo</div>
                            <div class="font-mono text-xs">{{ $expense->equipmentRequest->code }}</div>
                            <div class="text-slate-700">{{ $expense->equipmentRequest->title }}</div>
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
