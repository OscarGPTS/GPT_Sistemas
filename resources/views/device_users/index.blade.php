@extends('layouts.app')
@section('title', 'Usuarios de Impresión')
@section('page-title', 'Usuarios de Impresión')
@section('page-subtitle', 'Códigos de impresión asignados a personas y ubicaciones')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div class="text-sm text-slate-500"><span class="font-semibold text-slate-800">{{ $deviceUsers->total() }}</span> usuario(s) de impresión</div>
    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('device_users.manage'))
    <a href="{{ route('device_users.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nuevo usuario de impresión
    </a>
    @endif
</div>

<form method="GET" class="bg-white rounded-xl shadow-card border border-slate-200 p-4 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div class="md:col-span-2 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar por nombre, código de empleado, código de impresión..." class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg text-sm">
        </div>
        <select name="location_id" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Todas las ubicaciones</option>
            @foreach($flatLocations as $loc)
                <option value="{{ $loc['id'] }}" @selected(($filters['location_id'] ?? '') == $loc['id'])>{{ $loc['indented'] }}</option>
            @endforeach
        </select>
        <select name="is_active" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Todos los estados</option>
            <option value="1" @selected(($filters['is_active'] ?? '') === '1')>Activos</option>
            <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Inactivos</option>
        </select>
        <button class="bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-medium">Filtrar</button>
    </div>
</form>

<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="px-5 py-3">Empleado</th>
                <th class="px-5 py-3">Persona</th>
                <th class="px-5 py-3">Código impresión</th>
                <th class="px-5 py-3">Ubicación</th>
                <th class="px-5 py-3">Buzón</th>
                <th class="px-5 py-3">Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($deviceUsers as $du)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $du->employee_code ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-xs font-semibold">{{ strtoupper(substr($du->full_name, 0, 1)) }}</div>
                            <div>
                                <div class="font-medium text-slate-900">{{ $du->full_name }}</div>
                                @if($du->user_id)
                                    <div class="text-xs text-emerald-600">↪ Vinculado al usuario del sistema</div>
                                @elseif($du->email)
                                    <div class="text-xs text-slate-500">{{ $du->email }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        @if($du->isVisibleTo(auth()->user()))
                            <span class="font-mono text-sm font-bold bg-slate-100 px-2 py-1 rounded">{{ $du->print_code }}</span>
                        @else
                            <span class="font-mono text-sm text-slate-400">{{ $du->maskedPrintCode() }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-sm">
                        @if($du->location)
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" style="background: {{ $du->location->typeColor() }}"></span>
                                <span class="text-slate-700">{{ $du->location->fullName() }}</span>
                            </div>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs text-slate-500">{{ $du->mailbox ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @if($du->is_active)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-700"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('device_users.show', $du) }}" class="text-brand-600 hover:underline text-xs">Ver</a>
                        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('device_users.manage'))
                        <a href="{{ route('device_users.edit', $du) }}" class="text-slate-500 hover:underline text-xs ml-2">Editar</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-slate-400 text-sm">Sin usuarios de impresión.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($deviceUsers->hasPages())<div class="px-5 py-3 border-t border-slate-100">{{ $deviceUsers->links() }}</div>@endif
</div>
@endsection
