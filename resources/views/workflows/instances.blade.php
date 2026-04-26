@extends('layouts.app')
@section('title', 'Solicitudes')
@section('page-title', 'Solicitudes')
@section('page-subtitle', 'Instancias activas e históricas de flujos de aprobación')

@section('content')
<form method="GET" class="bg-white rounded-xl shadow-card border border-slate-200 p-4 mb-5">
    <div class="flex gap-3">
        <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Todos los estados</option>
            @foreach(['pending'=>'Pendiente','in_review'=>'En revisión','approved'=>'Aprobada','rejected'=>'Rechazada','cancelled'=>'Cancelada'] as $k=>$v)
                <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <button class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Filtrar</button>
    </div>
</form>

<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="px-5 py-3">ID</th>
                <th class="px-5 py-3">Flujo</th>
                <th class="px-5 py-3">Solicitante</th>
                <th class="px-5 py-3">Paso actual</th>
                <th class="px-5 py-3">Estado</th>
                <th class="px-5 py-3">Fecha</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($instances as $i)
                @php
                    $iCls = match($i->status) {
                        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'rejected' => 'bg-red-50 text-red-700 border-red-200',
                        'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
                        default => 'bg-amber-50 text-amber-700 border-amber-200',
                    };
                    $iLabel = ['approved'=>'Aprobada','rejected'=>'Rechazada','in_review'=>'En revisión','pending'=>'Pendiente','cancelled'=>'Cancelada'][$i->status] ?? $i->status;
                @endphp
                <tr class="hover:bg-slate-50 cursor-pointer" onclick="location='{{ route('workflows.instances.show', $i) }}'">
                    <td class="px-5 py-3 font-mono text-xs text-brand-600">#{{ $i->id }}</td>
                    <td class="px-5 py-3 font-medium text-slate-900">{{ $i->workflow?->name }}</td>
                    <td class="px-5 py-3 text-slate-700">{{ $i->initiator?->name }}</td>
                    <td class="px-5 py-3 text-slate-600 text-sm">Paso {{ $i->current_order }}</td>
                    <td class="px-5 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $iCls }}">{{ $iLabel }}</span></td>
                    <td class="px-5 py-3 text-xs text-slate-500">{{ $i->created_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400 text-sm">Sin solicitudes.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($instances->hasPages())<div class="px-5 py-3 border-t border-slate-100">{{ $instances->links() }}</div>@endif
</div>
@endsection
