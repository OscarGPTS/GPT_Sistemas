@extends('layouts.app')
@section('title', 'Auditoría')
@section('page-title', 'Auditoría del sistema')
@section('page-subtitle', 'Registro inmutable de eventos y acciones')

@section('content')
<form method="GET" class="bg-white rounded-xl shadow-card border border-slate-200 p-4 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input name="action" value="{{ $filters['action'] ?? '' }}" placeholder="Acción (ej. asset.created)"
               class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
        <input name="entity_type" value="{{ $filters['entity_type'] ?? '' }}" placeholder="Entidad (ej. Asset)"
               class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
        <input name="user_id" value="{{ $filters['user_id'] ?? '' }}" placeholder="ID usuario"
               class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
        <button class="bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-medium">Filtrar</button>
    </div>
</form>

<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="px-5 py-3">Fecha</th>
                <th class="px-5 py-3">Usuario</th>
                <th class="px-5 py-3">Acción</th>
                <th class="px-5 py-3">Entidad</th>
                <th class="px-5 py-3">ID</th>
                <th class="px-5 py-3">IP</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($logs as $log)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-5 py-2.5 text-xs text-slate-500 font-mono">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                    <td class="px-5 py-2.5 text-slate-700 text-sm">{{ $log->user?->name ?? 'Sistema' }}</td>
                    <td class="px-5 py-2.5 font-mono text-xs">
                        <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">{{ $log->action }}</span>
                    </td>
                    <td class="px-5 py-2.5 text-xs text-slate-600">{{ class_basename($log->entity_type ?? '') }}</td>
                    <td class="px-5 py-2.5 text-slate-500 text-xs">#{{ $log->entity_id }}</td>
                    <td class="px-5 py-2.5 text-xs text-slate-400 font-mono">{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400 text-sm">Sin registros de auditoría.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($logs->hasPages())<div class="px-5 py-3 border-t border-slate-100">{{ $logs->links() }}</div>@endif
</div>
@endsection
