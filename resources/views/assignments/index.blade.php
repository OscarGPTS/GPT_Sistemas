@extends('layouts.app')
@section('title', 'Asignaciones')
@section('page-title', 'Asignaciones de Activos')
@section('page-subtitle', 'Historial y asignaciones activas de equipos')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
    <div class="text-sm text-slate-500">
        <span class="font-semibold text-slate-800">{{ $assignments->total() }}</span> asignación(es)
    </div>
    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('assignments.manage'))
    <a href="{{ route('assignments.create') }}"
       class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nueva asignación
    </a>
    @endif
</div>

<form method="GET" class="bg-white rounded-xl shadow-card border border-slate-200 p-4 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <select name="user_id" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Todos los usuarios</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" @selected(($filters['user_id'] ?? '') == $u->id)>{{ $u->name }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-slate-600 px-3 py-2 bg-slate-50 rounded-lg border border-slate-200">
            <input type="checkbox" name="active" value="1" @checked($filters['active'] ?? false) class="rounded text-brand-600 focus:ring-brand-500">
            Solo asignaciones activas
        </label>
        <button class="bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-medium">Filtrar</button>
    </div>
</form>

<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Activo</th>
                    <th class="px-5 py-3">Usuario</th>
                    <th class="px-5 py-3">Asignado</th>
                    <th class="px-5 py-3">Devuelto</th>
                    <th class="px-5 py-3">Motivo</th>
                    <th class="px-5 py-3">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($assignments as $a)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3">
                            <a href="{{ route('assets.show', $a->asset_id) }}" class="font-mono text-brand-600 hover:underline">{{ $a->asset?->internal_code }}</a>
                            <div class="text-xs text-slate-500">{{ $a->asset?->brand }} {{ $a->asset?->model }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-semibold">{{ strtoupper(substr($a->user?->name ?? '?', 0, 1)) }}</div>
                                <span class="text-slate-700">{{ $a->user?->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-slate-600 text-sm">{{ $a->assigned_at?->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-slate-600 text-sm">{{ $a->returned_at?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-500 text-sm truncate max-w-xs">{{ $a->assignment_reason ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @if($a->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Activa
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">Cerrada</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400 text-sm">Sin asignaciones.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($assignments->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">{{ $assignments->links() }}</div>
    @endif
</div>
@endsection
