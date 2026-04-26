@extends('layouts.app')
@section('title', 'Tickets')
@section('page-title', 'Mesa de Servicio')
@section('page-subtitle', 'Gestión de incidencias, solicitudes y mantenimientos')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
    <div class="text-sm text-slate-500">
        <span class="font-semibold text-slate-800">{{ $tickets->total() }}</span> ticket(s) visible(s)
    </div>
    <a href="{{ route('tickets.create') }}"
       class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nuevo ticket
    </a>
</div>

<form method="GET" class="bg-white rounded-xl shadow-card border border-slate-200 p-4 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div class="md:col-span-2 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar por código o asunto..."
                   class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
        </div>
        <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Todos los estados</option>
            @foreach(['open'=>'Abierto','in_progress'=>'En progreso','on_hold'=>'En espera','resolved'=>'Resuelto','closed'=>'Cerrado','cancelled'=>'Cancelado'] as $k=>$v)
                <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <select name="priority" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Cualquier prioridad</option>
            @foreach(['low'=>'Baja','medium'=>'Media','high'=>'Alta','urgent'=>'Urgente'] as $k=>$v)
                <option value="{{ $k }}" @selected(($filters['priority'] ?? '') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <button class="bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-medium">Filtrar</button>
    </div>
</form>

<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Ticket</th>
                    <th class="px-5 py-3">Prioridad</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3">Solicitante</th>
                    <th class="px-5 py-3">Asignado</th>
                    <th class="px-5 py-3">Creado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($tickets as $t)
                    @php
                        $pCls = match($t->priority) {
                            'urgent' => 'bg-red-50 text-red-700 border-red-200',
                            'high' => 'bg-orange-50 text-orange-700 border-orange-200',
                            'medium' => 'bg-amber-50 text-amber-700 border-amber-200',
                            default => 'bg-slate-50 text-slate-600 border-slate-200',
                        };
                        $pLabel = ['urgent'=>'Urgente','high'=>'Alta','medium'=>'Media','low'=>'Baja'][$t->priority] ?? $t->priority;
                        $sCls = match($t->status) {
                            'open' => 'bg-rose-50 text-rose-700 border-rose-200',
                            'in_progress' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'on_hold' => 'bg-slate-50 text-slate-600 border-slate-200',
                            'resolved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'closed' => 'bg-slate-100 text-slate-500 border-slate-200',
                            default => 'bg-slate-50 text-slate-600 border-slate-200',
                        };
                        $sLabel = ['open'=>'Abierto','in_progress'=>'En progreso','on_hold'=>'En espera','resolved'=>'Resuelto','closed'=>'Cerrado','cancelled'=>'Cancelado'][$t->status] ?? $t->status;
                    @endphp
                    <tr class="hover:bg-slate-50 cursor-pointer transition" onclick="window.location='{{ route('tickets.show', $t) }}'">
                        <td class="px-5 py-3">
                            <div class="flex items-start gap-3">
                                <span class="font-mono text-xs text-slate-400 mt-0.5">{{ $t->code }}</span>
                                <div class="min-w-0">
                                    <div class="font-medium text-slate-900 truncate">{{ $t->subject }}</div>
                                    <div class="text-xs text-slate-500 capitalize">{{ ['incident'=>'Incidente','request'=>'Solicitud','maintenance'=>'Mantenimiento'][$t->type] ?? $t->type }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $pCls }}">{{ $pLabel }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $sCls }}">{{ $sLabel }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-semibold">{{ strtoupper(substr($t->requester?->name ?? '?', 0, 1)) }}</div>
                                <span class="text-slate-700 text-sm truncate">{{ $t->requester?->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-slate-600 text-sm">{{ $t->assignee?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs text-slate-500">{{ $t->created_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/></svg>
                                <div class="text-sm font-medium text-slate-600">Sin tickets</div>
                                <div class="text-xs mt-1">No hay tickets con los filtros actuales</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tickets->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
