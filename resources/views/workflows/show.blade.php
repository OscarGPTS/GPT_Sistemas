@extends('layouts.app')
@section('title', 'Flujo '.$workflow->name)
@section('page-title', $workflow->name)
@section('page-subtitle', 'Detalle y configuración del flujo')

@section('content')
<div class="flex flex-wrap items-center gap-2 mb-5">
    <span class="font-mono text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded">{{ $workflow->slug }}</span>
    <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">{{ $workflow->target_type }}</span>
    <span class="text-xs px-2.5 py-1 rounded-full {{ $workflow->mode === 'sequential' ? 'bg-blue-50 text-blue-700' : 'bg-violet-50 text-violet-700' }}">
        {{ $workflow->mode === 'sequential' ? 'Secuencial' : 'Paralelo' }}
    </span>
    @if($workflow->is_active)
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo</span>
    @endif
    <div class="ml-auto flex gap-2">
        <form method="POST" action="{{ route('workflows.instances.start', $workflow) }}">
            @csrf
            <button class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
                Iniciar solicitud →
            </button>
        </form>
        <a href="{{ route('workflows.edit', $workflow) }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium">Editar</a>
        <form method="POST" action="{{ route('workflows.destroy', $workflow) }}" onsubmit="return confirm('¿Eliminar flujo?')">
            @csrf @method('DELETE')
            <button class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-medium">Eliminar</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-card border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-900 mb-4">Pasos configurados</h3>
        <div class="space-y-2">
            @foreach($workflow->steps as $step)
                <div class="relative pl-12">
                    @if(!$loop->last)
                        <span class="absolute left-[18px] top-10 bottom-0 w-0.5 bg-slate-200"></span>
                    @endif
                    <div class="flex items-start gap-3">
                        <div class="absolute left-0 w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">{{ $step->order }}</div>
                        <div class="flex-1 border border-slate-200 rounded-lg p-3 bg-slate-50">
                            <div class="font-medium text-slate-900">{{ $step->name }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                @if($step->approver_type === 'role')
                                    <span class="inline-flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493"/></svg>Rol: <b>{{ $step->role?->label }}</b></span>
                                @elseif($step->approver_type === 'user')
                                    Usuario específico: <b>{{ $step->user?->name }}</b>
                                @else
                                    Jefe directo del solicitante
                                @endif
                                @if($step->is_optional)<span class="text-amber-600">· Opcional</span>@endif
                            </div>
                            @if($step->instructions)<div class="text-xs text-slate-600 mt-1">📝 {{ $step->instructions }}</div>@endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-900">Instancias recientes</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($instances as $inst)
                @php
                    $iCls = match($inst->status) {
                        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'rejected' => 'bg-red-50 text-red-700 border-red-200',
                        'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
                        default => 'bg-amber-50 text-amber-700 border-amber-200',
                    };
                @endphp
                <a href="{{ route('workflows.instances.show', $inst) }}" class="block px-5 py-3 hover:bg-slate-50">
                    <div class="flex justify-between items-start gap-2">
                        <div class="min-w-0">
                            <div class="font-mono text-xs text-brand-600">#{{ $inst->id }}</div>
                            <div class="text-sm text-slate-800 truncate">{{ $inst->initiator?->name }}</div>
                            <div class="text-xs text-slate-500">{{ $inst->created_at?->diffForHumans() }}</div>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full border {{ $iCls }}">{{ $inst->status }}</span>
                    </div>
                </a>
            @empty
                <div class="px-5 py-6 text-center text-slate-400 text-sm">Sin instancias.</div>
            @endforelse
        </div>
        @if($instances->hasPages())<div class="px-5 py-2 border-t">{{ $instances->links() }}</div>@endif
    </div>
</div>
@endsection
