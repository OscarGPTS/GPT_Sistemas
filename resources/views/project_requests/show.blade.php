@extends('layouts.app')
@section('title', 'Solicitud '.$request->code)
@section('page-title', $request->code)
@section('page-subtitle', $request->name)

@section('content')
@php
    $sCls = match($request->status) {
        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'rejected' => 'bg-red-50 text-red-700 border-red-200',
        'converted' => 'bg-blue-50 text-blue-700 border-blue-200',
        'in_review' => 'bg-amber-50 text-amber-700 border-amber-200',
        'draft' => 'bg-slate-100 text-slate-600 border-slate-200',
        default => 'bg-slate-100',
    };
    $sLabel = ['draft'=>'Borrador','in_review'=>'En revisión','approved'=>'Aprobada','rejected'=>'Rechazada','converted'=>'Convertida','cancelled'=>'Cancelada'][$request->status] ?? $request->status;
@endphp

<div class="bg-white rounded-xl shadow-card border border-slate-200 p-5 mb-5 flex flex-wrap items-start justify-between gap-3">
    <div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $sCls }}">{{ $sLabel }}</span>
        <h1 class="text-2xl font-bold text-slate-900 mt-2">{{ $request->name }}</h1>
        <div class="text-sm text-slate-500 mt-1">{{ $request->requester?->name }} · {{ $request->created_at?->format('d/m/Y H:i') }}</div>
    </div>
    <div class="flex flex-wrap gap-2">
        @if($request->status === 'draft' && (auth()->id() === $request->requester_id || auth()->user()->isAdmin()))
            <form method="POST" action="{{ route('project_requests.submit', $request) }}">
                @csrf
                <button class="bg-gradient-to-r from-brand-600 to-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">Enviar a aprobación →</button>
            </form>
        @endif
        @if($request->status === 'approved' && ! $request->project_id && (auth()->user()->isAdmin() || auth()->user()->hasRole(['manager'])))
            <form method="POST" action="{{ route('project_requests.convert', $request) }}">
                @csrf
                <button class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium">✓ Convertir a proyecto</button>
            </form>
        @endif
        @if($request->project_id)
            <a href="{{ route('projects.board', $request->project) }}" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium">→ Ver proyecto</a>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Detalle</h3>
            <p class="text-sm text-slate-700 whitespace-pre-wrap mb-4">{{ $request->description }}</p>
            @if($request->justification)
                <h4 class="text-xs font-semibold text-slate-500 uppercase mb-1">Justificación</h4>
                <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $request->justification }}</p>
            @endif
        </div>
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Información</h3>
            <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div><dt class="text-xs text-slate-500 uppercase">Impacto</dt><dd class="capitalize">{{ ['low'=>'Bajo','medium'=>'Medio','high'=>'Alto'][$request->impact] ?? $request->impact }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">Prioridad</dt><dd class="capitalize">{{ ['urgent'=>'Urgente','high'=>'Alta','medium'=>'Media','low'=>'Baja'][$request->priority] ?? $request->priority }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">Presupuesto</dt><dd>{{ $request->budget_estimate ? '$ '.number_format((float) $request->budget_estimate, 2) : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">Área</dt><dd>{{ $request->expected_area ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">Inicio deseado</dt><dd>{{ $request->desired_start_date?->format('d/m/Y') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">Fin deseado</dt><dd>{{ $request->desired_end_date?->format('d/m/Y') ?? '—' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="space-y-5">
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Flujo de aprobación</h3>
            @if($request->workflowInstance)
                <div class="space-y-2">
                    @foreach($request->workflowInstance->workflow->steps as $step)
                        @php
                            $approval = $request->workflowInstance->approvals->where('step_id', $step->id)->whereIn('decision', ['approved','rejected'])->first();
                            $isCurrent = $step->order === $request->workflowInstance->current_order && $request->workflowInstance->status === 'in_review';
                            $cls = $approval?->decision === 'approved' ? 'bg-emerald-500' : ($approval?->decision === 'rejected' ? 'bg-red-500' : ($isCurrent ? 'bg-amber-500 animate-pulse' : 'bg-slate-300'));
                        @endphp
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full {{ $cls }} text-white text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $step->order }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-slate-800">{{ $step->name }}</div>
                                @if($approval)
                                    <div class="text-xs text-slate-500">{{ $approval->approver?->name }} · {{ $approval->decided_at?->format('d/m/Y H:i') }}</div>
                                    @if($approval->comment)
                                        <div class="text-xs text-slate-600 italic mt-0.5">"{{ $approval->comment }}"</div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('workflows.instances.show', $request->workflowInstance) }}" class="mt-3 block text-xs text-brand-600 hover:underline">Ver flujo completo →</a>
            @else
                <div class="text-sm text-slate-500">La solicitud aún no se ha enviado a aprobación.</div>
            @endif
        </div>
    </div>
</div>
@endsection
