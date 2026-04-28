@extends('layouts.app')
@section('title', 'Auditoría de accesos')
@section('page-title', 'Auditoría de accesos')
@section('page-subtitle', 'Registro inmutable de cada interacción con el vault de credenciales')

@section('content')
<form method="GET" class="bg-white rounded-xl shadow-card border border-slate-200 p-4 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input name="access_id" value="{{ $filters['access_id'] ?? '' }}" placeholder="ID de acceso" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
        <select name="action" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Todas las acciones</option>
            @foreach(\App\Models\AccessLog::ACTIONS as $k => $v)
                <option value="{{ $k }}" @selected(($filters['action'] ?? '') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <input name="user_id" value="{{ $filters['user_id'] ?? '' }}" placeholder="ID usuario" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
        <button class="bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-medium">Filtrar</button>
    </div>
</form>

<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="px-5 py-3">Fecha</th>
                <th class="px-5 py-3">Usuario</th>
                <th class="px-5 py-3">Acceso</th>
                <th class="px-5 py-3">Acción</th>
                <th class="px-5 py-3">Campo</th>
                <th class="px-5 py-3">Motivo</th>
                <th class="px-5 py-3">IP</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($logs as $log)
                @php
                    $cls = match($log->action) {
                        'reveal' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'failed_otp' => 'bg-red-50 text-red-700 border-red-200',
                        'rotate' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'create' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'delete' => 'bg-red-50 text-red-700 border-red-200',
                        default => 'bg-slate-50 text-slate-600 border-slate-200',
                    };
                @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-2.5 text-xs font-mono text-slate-500">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                    <td class="px-5 py-2.5 text-slate-700">{{ $log->user?->name ?? '—' }}</td>
                    <td class="px-5 py-2.5">
                        <a href="{{ route('accesses.show', $log->access_id) }}" class="font-mono text-xs text-brand-600">{{ $log->access?->code ?? '#'.$log->access_id }}</a>
                    </td>
                    <td class="px-5 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs border {{ $cls }}">{{ $log->actionLabel() }}</span></td>
                    <td class="px-5 py-2.5 text-xs text-slate-600 font-mono">{{ $log->field ?? '—' }}</td>
                    <td class="px-5 py-2.5 text-xs text-slate-500 italic max-w-xs truncate">{{ $log->reason ?? '—' }}</td>
                    <td class="px-5 py-2.5 text-xs text-slate-400 font-mono">{{ $log->ip_address ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-slate-400 text-sm">Sin registros de auditoría.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($logs->hasPages())<div class="px-5 py-3 border-t border-slate-100">{{ $logs->links() }}</div>@endif
</div>

<div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-800">
    🔒 Estos registros son <b>inmutables</b>: no pueden editarse ni eliminarse. Cumplen requisitos de auditoría y cumplimiento.
</div>
@endsection
