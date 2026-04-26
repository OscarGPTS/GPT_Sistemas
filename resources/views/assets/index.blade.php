@extends('layouts.app')
@section('title', 'Activos')
@section('page-title', 'Inventario de Activos')
@section('page-subtitle', 'Gestión completa del parque de equipos')

@section('content')
<!-- Toolbar -->
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
    <div class="flex items-center gap-2">
        <div class="text-sm text-slate-500">Total: <span class="font-semibold text-slate-800">{{ $assets->total() }}</span> activos</div>
    </div>
    <div class="flex gap-2">
        @can('viewAny', App\Models\Asset::class)
        @endcan
        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('assets.import'))
        <a href="{{ route('assets.import.form') }}"
           class="inline-flex items-center gap-2 bg-white border border-slate-300 text-slate-700 px-3.5 py-2 rounded-lg hover:bg-slate-50 text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Importar CSV
        </a>
        @endif
        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('assets.create'))
        <a href="{{ route('assets.create') }}"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nuevo activo
        </a>
        @endif
    </div>
</div>

<!-- Filters -->
<form method="GET" class="bg-white rounded-xl shadow-card border border-slate-200 p-4 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div class="md:col-span-2 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar por código, serie, marca o modelo..."
                   class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
        </div>
        <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            <option value="">Todos los estados</option>
            @foreach(['available'=>'Disponible','assigned'=>'Asignado','in_maintenance'=>'En mantenimiento','retired'=>'Baja','lost'=>'Extraviado'] as $k=>$v)
                <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <select name="category_id" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            <option value="">Todas las categorías</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(($filters['category_id'] ?? '') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button class="flex-1 bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-medium">Filtrar</button>
            <a href="{{ route('assets.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-700 text-sm">Limpiar</a>
        </div>
    </div>
</form>

<!-- Table -->
<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Código</th>
                    <th class="px-5 py-3">Equipo</th>
                    <th class="px-5 py-3">Serie</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3">Asignado a</th>
                    <th class="px-5 py-3">Ubicación</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($assets as $a)
                    @php
                        $statusCls = match($a->status) {
                            'available' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'assigned' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'in_maintenance' => 'bg-violet-50 text-violet-700 border-violet-200',
                            'retired' => 'bg-slate-100 text-slate-600 border-slate-200',
                            'lost' => 'bg-red-50 text-red-700 border-red-200',
                            default => 'bg-slate-100 text-slate-600 border-slate-200',
                        };
                        $statusLabel = match($a->status) {
                            'available' => 'Disponible',
                            'assigned' => 'Asignado',
                            'in_maintenance' => 'Mantenimiento',
                            'retired' => 'Baja',
                            'lost' => 'Extraviado',
                            default => $a->status,
                        };
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3 font-mono text-slate-900 font-medium">{{ $a->internal_code }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-medium text-slate-900 truncate">{{ $a->brand }} {{ $a->model }}</div>
                                    <div class="text-xs text-slate-500">{{ $a->category?->name ?? $a->type }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-slate-500 font-mono text-xs">{{ $a->serial_number ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusCls }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-700">
                            @if($a->currentAssignment?->user)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-semibold">{{ strtoupper(substr($a->currentAssignment->user->name, 0, 1)) }}</div>
                                    <span class="truncate">{{ $a->currentAssignment->user->name }}</span>
                                </div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-600 text-sm">{{ $a->location ?? '—' }}</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('assets.show', $a) }}" class="text-brand-600 hover:text-brand-700 font-medium">Ver</a>
                            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('assets.update'))
                                <a href="{{ route('assets.edit', $a) }}" class="text-slate-500 hover:text-slate-700 font-medium ml-3">Editar</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <div class="text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                                <div class="text-sm font-medium text-slate-600">Sin resultados</div>
                                <div class="text-xs mt-1">No se encontraron activos con esos filtros</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($assets->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">{{ $assets->links() }}</div>
    @endif
</div>
@endsection
