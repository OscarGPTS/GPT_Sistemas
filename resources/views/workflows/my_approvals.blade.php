@extends('layouts.app')
@section('title', 'Mis aprobaciones')
@section('page-title', 'Mis aprobaciones pendientes')
@section('page-subtitle', 'Solicitudes que requieren tu decisión')

@section('content')
<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
    @forelse($approvals as $a)
        <div class="p-5 border-b border-slate-100 last:border-0 hover:bg-slate-50 transition">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="w-11 h-11 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-mono text-xs text-slate-400">#{{ $a->instance_id }}</span>
                        <a href="{{ route('workflows.instances.show', $a->instance_id) }}" class="font-semibold text-slate-900 hover:text-brand-700">
                            {{ $a->instance->workflow->name }}
                        </a>
                    </div>
                    <div class="text-sm text-slate-500 mt-0.5">
                        Solicitante: <span class="text-slate-700">{{ $a->instance->initiator?->name }}</span> ·
                        Paso: <span class="text-slate-700">{{ $a->step->name }}</span>
                    </div>
                    @if($a->step->instructions)
                        <div class="mt-2 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2 text-xs text-blue-800">
                            <b>Instrucciones:</b> {{ $a->step->instructions }}
                        </div>
                    @endif
                </div>
                <a href="{{ route('workflows.instances.show', $a->instance_id) }}"
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
                    Revisar
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3"/></svg>
                </a>
            </div>
        </div>
    @empty
        <div class="p-16 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="font-medium text-slate-800">Todo al día</div>
            <div class="text-sm text-slate-500 mt-1">No tienes aprobaciones pendientes</div>
        </div>
    @endforelse
</div>
@endsection
