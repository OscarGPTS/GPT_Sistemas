@extends('layouts.app')
@section('title', 'Flujos')
@section('page-title', 'Flujos de Aprobación')
@section('page-subtitle', 'Configura flujos dinámicos de aprobación multi-paso')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div class="text-sm text-slate-500"><span class="font-semibold text-slate-800">{{ $workflows->count() }}</span> flujo(s)</div>
    <a href="{{ route('workflows.create') }}"
       class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nuevo flujo
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($workflows as $wf)
        <div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden hover:shadow-md transition">
            <div class="p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </div>
                    @if($wf->is_active)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Inactivo</span>
                    @endif
                </div>
                <h3 class="font-semibold text-slate-900 mb-1">{{ $wf->name }}</h3>
                <div class="text-xs font-mono text-slate-400 mb-3">{{ $wf->slug }}</div>
                <div class="flex items-center gap-4 text-xs text-slate-500 mb-4">
                    <div class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75"/></svg>
                        {{ $wf->steps_count }} pasos
                    </div>
                    <div class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15"/></svg>
                        {{ $wf->instances_count }} instancias
                    </div>
                </div>
                <div class="text-xs text-slate-600 mb-1"><span class="text-slate-400">Tipo:</span> {{ $wf->target_type }}</div>
                <div class="text-xs text-slate-600"><span class="text-slate-400">Modo:</span> {{ $wf->mode === 'sequential' ? 'Secuencial' : 'Paralelo' }}</div>
            </div>
            <div class="border-t border-slate-100 px-5 py-3 flex justify-between bg-slate-50">
                <a href="{{ route('workflows.show', $wf) }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Ver detalles</a>
                <a href="{{ route('workflows.edit', $wf) }}" class="text-sm text-slate-500 hover:text-slate-700 font-medium">Editar</a>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white rounded-xl shadow-card border border-slate-200 p-12 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            <div class="text-sm font-medium text-slate-600">Sin flujos</div>
            <div class="text-xs text-slate-500 mt-1">Crea tu primer flujo de aprobación</div>
        </div>
    @endforelse
</div>
@endsection
