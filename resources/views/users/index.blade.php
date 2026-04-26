@extends('layouts.app')
@section('title', 'Usuarios')
@section('page-title', 'Usuarios del sistema')
@section('page-subtitle', 'Gestión de colaboradores, roles y permisos')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div class="text-sm text-slate-500"><span class="font-semibold text-slate-800">{{ $users->total() }}</span> usuario(s)</div>
    <a href="{{ route('users.create') }}"
       class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nuevo usuario
    </a>
</div>

<form method="GET" class="bg-white rounded-xl shadow-card border border-slate-200 p-4 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar por nombre, email o código..."
                   class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
        </div>
        <input name="department" value="{{ $filters['department'] ?? '' }}" placeholder="Área / Departamento"
               class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
        <button class="bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-medium">Filtrar</button>
    </div>
</form>

<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Usuario</th>
                    <th class="px-5 py-3">Código</th>
                    <th class="px-5 py-3">Área / Puesto</th>
                    <th class="px-5 py-3">Jefe</th>
                    <th class="px-5 py-3">Roles</th>
                    <th class="px-5 py-3">Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($users as $u)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-sm font-semibold">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                                <div>
                                    <div class="font-medium text-slate-900">{{ $u->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $u->employee_code ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <div class="text-slate-800 text-sm">{{ $u->department ?? '—' }}</div>
                            <div class="text-xs text-slate-500">{{ $u->position ?? '—' }}</div>
                        </td>
                        <td class="px-5 py-3 text-slate-600 text-sm">{{ $u->manager?->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($u->roles as $r)
                                    <span class="text-xs bg-brand-50 text-brand-700 border border-brand-100 rounded-full px-2 py-0.5">{{ $r->label }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('users.toggle', $u) }}" class="inline">
                                @csrf
                                @if($u->is_active)
                                    <button class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo
                                    </button>
                                @else
                                    <button class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200 transition">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactivo
                                    </button>
                                @endif
                            </form>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('users.edit', $u) }}" class="text-brand-600 hover:text-brand-700 font-medium text-sm">Editar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">{{ $users->links() }}</div>
    @endif
</div>
@endsection
