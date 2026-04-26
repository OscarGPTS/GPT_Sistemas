@extends('layouts.app')
@section('title', 'Solicitud #'.$instance->id)
@section('page-title', 'Solicitud #'.$instance->id)
@section('page-subtitle', $instance->workflow->name)

@section('content')
@php
    $iCls = match($instance->status) {
        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'rejected' => 'bg-red-50 text-red-700 border-red-200',
        'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
        default => 'bg-amber-50 text-amber-700 border-amber-200',
    };
    $iLabel = ['approved'=>'Aprobada','rejected'=>'Rechazada','in_review'=>'En revisión','pending'=>'Pendiente','cancelled'=>'Cancelada'][$instance->status] ?? $instance->status;
@endphp

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center font-semibold">{{ strtoupper(substr($instance->initiator?->name ?? '?', 0, 1)) }}</div>
        <div>
            <div class="text-sm font-medium text-slate-900">{{ $instance->initiator?->name }}</div>
            <div class="text-xs text-slate-500">Creada {{ $instance->created_at?->format('d/m/Y H:i') }}</div>
        </div>
    </div>
    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium border {{ $iCls }}">{{ $iLabel }}</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <!-- Progress steps -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-card border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-900 mb-4">Progreso del flujo</h3>
        <div class="space-y-3">
            @foreach($instance->workflow->steps as $step)
                @php
                    $stepApprovals = $instance->approvals->where('step_id', $step->id);
                    $decided = $stepApprovals->whereIn('decision', ['approved','rejected'])->first();
                    $isCurrent = $step->order === $instance->current_order && $instance->status === 'in_review';
                    $isPast = $step->order < $instance->current_order;
                    $ringCls = $decided?->decision === 'approved' ? 'bg-emerald-500 text-white' :
                               ($decided?->decision === 'rejected' ? 'bg-red-500 text-white' :
                               ($isCurrent ? 'bg-amber-500 text-white animate-pulse' : 'bg-slate-200 text-slate-500'));
                @endphp
                <div class="relative pl-12">
                    @if(!$loop->last)
                        <span class="absolute left-[18px] top-10 bottom-0 w-0.5 {{ $isPast ? 'bg-emerald-400' : 'bg-slate-200' }}"></span>
                    @endif
                    <div class="absolute left-0 w-9 h-9 rounded-full {{ $ringCls }} flex items-center justify-center text-sm font-bold shadow-sm">
                        @if($decided?->decision === 'approved')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        @elseif($decided?->decision === 'rejected')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        @else
                            {{ $step->order }}
                        @endif
                    </div>
                    <div class="border {{ $isCurrent ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-white' }} rounded-lg p-4">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <div>
                                <div class="font-medium text-slate-900">{{ $step->name }}</div>
                                <div class="text-xs text-slate-500">
                                    @if($step->approver_type === 'role') Rol: <b>{{ $step->role?->label }}</b>
                                    @elseif($step->approver_type === 'user') Usuario: <b>{{ $step->user?->name }}</b>
                                    @else Jefe directo del solicitante
                                    @endif
                                </div>
                            </div>
                            @if($decided)
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $decided->decision === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $decided->decision === 'approved' ? 'Aprobado' : 'Rechazado' }}</span>
                            @elseif($isCurrent)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-200 text-amber-900">Pendiente</span>
                            @endif
                        </div>
                        @if($decided)
                            <div class="mt-2 text-xs text-slate-600 bg-white border border-slate-200 rounded px-2.5 py-1.5">
                                Por <b>{{ $decided->approver?->name ?? '—' }}</b> · {{ $decided->decided_at?->format('d/m/Y H:i') }}
                                @if($decided->comment)<div class="italic text-slate-500 mt-0.5">"{{ $decided->comment }}"</div>@endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="space-y-5">
        <!-- Action -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Tu decisión</h3>
            @if($instance->status === 'in_review')
                <form method="POST" action="{{ route('workflows.instances.decide', $instance) }}" class="space-y-3">
                    @csrf
                    <textarea name="comment" rows="3" class="w-full border border-slate-300 rounded-lg p-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100" placeholder="Comentario (opcional)"></textarea>
                    <div class="grid grid-cols-2 gap-2">
                        <button name="decision" value="approved" class="inline-flex items-center justify-center gap-1.5 bg-emerald-600 hover:bg-emerald-500 text-white py-2 rounded-lg text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Aprobar
                        </button>
                        <button name="decision" value="rejected" class="inline-flex items-center justify-center gap-1.5 bg-red-600 hover:bg-red-500 text-white py-2 rounded-lg text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Rechazar
                        </button>
                    </div>
                </form>
            @else
                <div class="text-sm text-slate-500">Esta solicitud ya está cerrada.</div>
            @endif
        </div>

        <!-- Payload -->
        @if($instance->payload)
            <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">Datos de la solicitud</h3>
                <dl class="space-y-2 text-sm">
                    @foreach($instance->payload as $k => $v)
                        <div>
                            <dt class="text-xs text-slate-500 uppercase capitalize">{{ str_replace('_', ' ', $k) }}</dt>
                            <dd class="text-slate-800 mt-0.5">{{ is_array($v) ? json_encode($v) : $v }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </div>
</div>
@endsection
