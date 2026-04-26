@extends('layouts.app')
@section('title', 'Mantenimiento')
@section('page-title', 'Mantenimiento')
@section('page-subtitle', 'Programaciones, registros y alertas de mantenimiento')

@section('content')
<div class="flex flex-wrap items-center justify-end gap-2 mb-5">
    <form method="POST" action="{{ route('maintenance.generate') }}">
        @csrf
        <button class="inline-flex items-center gap-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-3.5 py-2 rounded-lg text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
            Generar próximos
        </button>
    </form>
    <a href="{{ route('maintenance.schedules.create') }}"
       class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-3.5 py-2 rounded-lg text-sm font-medium transition">
        + Programación
    </a>
    <a href="{{ route('maintenance.records.create') }}"
       class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nuevo registro
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-900">Registros de mantenimiento</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3">Activo</th>
                        <th class="px-5 py-3">Título</th>
                        <th class="px-5 py-3">Tipo</th>
                        <th class="px-5 py-3">Programado</th>
                        <th class="px-5 py-3">Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($records as $r)
                        @php
                            $sCls = match($r->status) {
                                'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'scheduled' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'in_progress' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
                                default => 'bg-slate-50 text-slate-600 border-slate-200',
                            };
                            $sLabel = ['scheduled'=>'Programado','in_progress'=>'En progreso','completed'=>'Completado','cancelled'=>'Cancelado'][$r->status] ?? $r->status;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3">
                                <a href="{{ route('assets.show', $r->asset_id) }}" class="font-mono text-brand-600 hover:underline text-xs">{{ $r->asset?->internal_code }}</a>
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-medium text-slate-900">{{ $r->title }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $r->type === 'preventive' ? 'bg-blue-50 text-blue-700' : 'bg-orange-50 text-orange-700' }}">
                                    {{ $r->type === 'preventive' ? 'Preventivo' : 'Correctivo' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-600 text-sm">{{ $r->scheduled_date?->format('d/m/Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $sCls }}">{{ $sLabel }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if($r->status !== 'completed' && (auth()->user()->isAdmin() || auth()->user()->hasPermission('maintenance.manage')))
                                    <form method="POST" action="{{ route('maintenance.records.complete', $r) }}" class="inline" onsubmit="return confirm('¿Marcar como completado?')">
                                        @csrf
                                        <input type="hidden" name="performed_date" value="{{ now()->format('Y-m-d') }}">
                                        <button class="text-emerald-600 hover:text-emerald-700 text-xs font-medium">Completar →</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400 text-sm">Sin registros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())<div class="px-5 py-3 border-t border-slate-100">{{ $records->links() }}</div>@endif
    </div>

    <div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-900">Programaciones activas</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($schedules as $s)
                @php
                    $overdue = $s->next_due_at && $s->next_due_at->isPast();
                    $daysUntil = $s->next_due_at ? now()->startOfDay()->diffInDays($s->next_due_at, false) : null;
                @endphp
                <div class="p-4 text-sm">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <div class="font-medium text-slate-900">{{ $s->title }}</div>
                        @if($overdue)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200">Vencido</span>
                        @elseif($daysUntil !== null && $daysUntil <= 15)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">Próximo</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500">
                        <span class="font-mono">{{ $s->asset?->internal_code }}</span> · {{ $s->frequency }}
                    </div>
                    <div class="text-xs text-slate-600 mt-1">
                        Siguiente: <b>{{ $s->next_due_at?->format('d/m/Y') }}</b>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-sm">Sin programaciones.</div>
            @endforelse
        </div>
        @if($schedules->hasPages())<div class="px-5 py-3 border-t border-slate-100">{{ $schedules->links() }}</div>@endif
    </div>
</div>
@endsection
