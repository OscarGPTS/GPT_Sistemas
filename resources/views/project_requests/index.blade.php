@extends('layouts.app')
@section('title', 'Solicitudes de Proyecto')
@section('page-title', 'Solicitudes de Proyecto')
@section('page-subtitle', 'Solicita y aprueba la creación de nuevos proyectos')

@section('content')
<div class="flex justify-between mb-5">
    <div class="text-sm text-slate-500"><span class="font-semibold text-slate-800">{{ $requests->total() }}</span> solicitud(es)</div>
    <a href="{{ route('project_requests.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nueva solicitud
    </a>
</div>

<form method="GET" class="bg-white rounded-xl shadow-card border border-slate-200 p-4 mb-5">
    <div class="flex gap-3">
        <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Todos los estados</option>
            @foreach(['draft'=>'Borrador','in_review'=>'En revisión','approved'=>'Aprobada','rejected'=>'Rechazada','converted'=>'Convertida','cancelled'=>'Cancelada'] as $k=>$v)
                <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $v }}</option>
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
                <th class="px-5 py-3">Prioridad</th>
                <th class="px-5 py-3">Estado</th>
                <th class="px-5 py-3">Fecha</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($requests as $r)
                @php
                    $sCls = match($r->status) {
                        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'rejected' => 'bg-red-50 text-red-700 border-red-200',
                        'converted' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'in_review' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'draft' => 'bg-slate-100 text-slate-600 border-slate-200',
                        default => 'bg-slate-100',
                    };
                    $sLabel = ['draft'=>'Borrador','in_review'=>'En revisión','approved'=>'Aprobada','rejected'=>'Rechazada','converted'=>'Convertida','cancelled'=>'Cancelada'][$r->status] ?? $r->status;
                @endphp
                <tr class="hover:bg-slate-50 cursor-pointer" onclick="location='{{ route('project_requests.show', $r) }}'">
                    <td class="px-5 py-3 font-mono text-xs text-brand-600">{{ $r->code }}</td>
                    <td class="px-5 py-3">
                        <div class="font-medium text-slate-900">{{ $r->name }}</div>
                        <div class="text-xs text-slate-500 truncate max-w-md">{{ Str::limit($r->description, 80) }}</div>
                    </td>
                    <td class="px-5 py-3 text-slate-700">{{ $r->requester?->name }}</td>
                    <td class="px-5 py-3 capitalize">{{ ['urgent'=>'Urgente','high'=>'Alta','medium'=>'Media','low'=>'Baja'][$r->priority] ?? $r->priority }}</td>
                    <td class="px-5 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $sCls }}">{{ $sLabel }}</span></td>
                    <td class="px-5 py-3 text-xs text-slate-500">{{ $r->created_at?->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400 text-sm">Sin solicitudes.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($requests->hasPages())<div class="px-5 py-3 border-t border-slate-100">{{ $requests->links() }}</div>@endif
</div>
@endsection
