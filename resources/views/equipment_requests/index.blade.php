@extends('layouts.app')
@section('title', 'Solicitudes de Equipo')
@section('page-title', 'Solicitudes de Equipo')
@section('page-subtitle', 'Compras desde solicitud hasta entrega')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="text-sm text-slate-500"><span class="font-semibold text-slate-800">{{ $requests->total() }}</span> solicitud(es)</div>
    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('equipment_requests.create'))
    <a href="{{ route('equipment_requests.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Solicitar equipo
    </a>
    @endif
</div>

<form method="GET" class="bg-white rounded-xl shadow-card border border-slate-200 p-4 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar por código o título..." class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg text-sm">
        </div>
        <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Todos los estados</option>
            @foreach($statuses as $k => $label)
                <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-medium">Filtrar</button>
    </div>
</form>

<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="px-5 py-3">Código</th>
                <th class="px-5 py-3">Solicitud</th>
                <th class="px-5 py-3">Solicitante</th>
                <th class="px-5 py-3">Items</th>
                <th class="px-5 py-3 text-right">Total estimado</th>
                <th class="px-5 py-3">Prioridad</th>
                <th class="px-5 py-3">Estado</th>
                <th class="px-5 py-3">Fecha</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($requests as $r)
                <tr class="hover:bg-slate-50 cursor-pointer" onclick="location='{{ route('equipment_requests.show', $r) }}'">
                    <td class="px-5 py-3 font-mono text-xs text-brand-600">{{ $r->code }}</td>
                    <td class="px-5 py-3">
                        <div class="font-medium text-slate-900">{{ $r->title }}</div>
                        <div class="text-xs text-slate-500 truncate max-w-md">{{ Str::limit($r->justification ?? '', 80) }}</div>
                    </td>
                    <td class="px-5 py-3 text-slate-700">{{ $r->requester?->name }}</td>
                    <td class="px-5 py-3 text-slate-600 text-sm">{{ $r->items->count() }} ({{ $r->items->sum('quantity') }} unidades)</td>
                    <td class="px-5 py-3 text-right font-mono text-sm">$ {{ number_format($r->totalEstimated(), 2) }}</td>
                    <td class="px-5 py-3 capitalize text-xs">{{ ['urgent'=>'Urgente','high'=>'Alta','medium'=>'Media','low'=>'Baja'][$r->priority] ?? $r->priority }}</td>
                    <td class="px-5 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $r->statusBadge() }}">{{ $r->statusLabel() }}</span></td>
                    <td class="px-5 py-3 text-xs text-slate-500">{{ $r->created_at?->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-5 py-12 text-center text-slate-400 text-sm">Sin solicitudes.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($requests->hasPages())<div class="px-5 py-3 border-t border-slate-100">{{ $requests->links() }}</div>@endif
</div>
@endsection
