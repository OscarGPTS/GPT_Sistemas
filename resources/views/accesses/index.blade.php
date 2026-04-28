@extends('layouts.app')
@section('title', 'Vault de Accesos')
@section('page-title', 'Vault de Accesos')
@section('page-subtitle', 'Credenciales seguras de servidores, cámaras, WiFi y servicios')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex gap-3 text-sm">
        <div class="bg-white rounded-lg border border-slate-200 px-4 py-2 shadow-card">
            <span class="text-slate-500">Total:</span>
            <span class="font-bold text-slate-900 ml-1">{{ $accesses->total() }}</span>
        </div>
    </div>
    <div class="flex gap-2">
        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('accesses.audit'))
            <a href="{{ route('accesses.logs') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-3.5 py-2 rounded-lg text-sm font-medium">📜 Auditoría</a>
        @endif
        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('accesses.create'))
            <a href="{{ route('accesses.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Nuevo acceso
            </a>
        @endif
    </div>
</div>

<form method="GET" class="bg-white rounded-xl shadow-card border border-slate-200 p-4 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div class="md:col-span-2 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar por nombre, IP, hostname..." class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg text-sm">
        </div>
        <select name="type_id" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Todos los tipos</option>
            @foreach($types as $t)
                <option value="{{ $t->id }}" @selected(($filters['type_id'] ?? '') == $t->id)>{{ $t->name }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-slate-600 px-3 py-2 bg-slate-50 rounded-lg border border-slate-200">
            <input type="checkbox" name="stale" value="1" @checked($filters['stale'] ?? false) class="rounded text-brand-600">
            Solo "stale" (>90 días)
        </label>
        <button class="bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-medium">Filtrar</button>
    </div>
</form>

<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="px-5 py-3">Código</th>
                <th class="px-5 py-3">Tipo</th>
                <th class="px-5 py-3">Acceso</th>
                <th class="px-5 py-3">Host / URL</th>
                <th class="px-5 py-3">Usuario</th>
                <th class="px-5 py-3">Contraseña</th>
                <th class="px-5 py-3">Rotación</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($accesses as $a)
                @php $stale = $a->isStale(); @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-mono text-xs text-brand-600">{{ $a->code }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded-full" style="background: {{ $a->type?->color }}15; color: {{ $a->type?->color }}">
                            <span class="w-2 h-2 rounded-full" style="background: {{ $a->type?->color }}"></span>
                            {{ $a->type?->name }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="font-medium text-slate-900">{{ $a->name }}</div>
                        @if($a->location)<div class="text-xs text-slate-500">{{ $a->location->name }}</div>@endif
                    </td>
                    <td class="px-5 py-3 font-mono text-xs text-slate-600">
                        {{ $a->ip ?? $a->hostname ?? Str::limit($a->url ?? '—', 30) }}
                    </td>
                    <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $a->maskedUsername() }}</td>
                    <td class="px-5 py-3">
                        <span class="font-mono text-sm text-slate-400 select-none">{{ $a->maskedPassword() }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs">
                        @if($stale)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200" title="Última rotación: {{ optional($a->last_rotated_at)->format('d/m/Y') ?? 'nunca' }}">
                                ⚠ Stale ({{ optional($a->last_rotated_at)->diffForHumans() ?? 'nunca' }})
                            </span>
                        @else
                            <span class="text-slate-500">{{ $a->last_rotated_at?->diffForHumans() }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('accesses.show', $a) }}" class="text-brand-600 hover:underline text-xs">Abrir</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-5 py-12 text-center text-slate-400 text-sm">Sin accesos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($accesses->hasPages())<div class="px-5 py-3 border-t border-slate-100">{{ $accesses->links() }}</div>@endif
</div>

<div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800">
    🔐 <b>Política de seguridad:</b> Las contraseñas están cifradas con AES-256. Para revelarlas necesitas un código OTP enviado a tu correo. Cada acceso queda registrado en la auditoría.
</div>
@endsection
