@extends('layouts.app')
@section('title', 'Ubicaciones')
@section('page-title', 'Ubicaciones')
@section('page-subtitle', 'Estructura jerárquica de sitios, edificios, pisos y áreas')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div class="flex gap-3 text-sm">
        <div class="bg-white rounded-lg border border-slate-200 px-4 py-2 shadow-card">
            <span class="text-slate-500">Total:</span>
            <span class="font-bold text-slate-900 ml-1">{{ $totalCount }}</span>
        </div>
        <div class="bg-white rounded-lg border border-slate-200 px-4 py-2 shadow-card">
            <span class="text-slate-500">Activas:</span>
            <span class="font-bold text-emerald-600 ml-1">{{ $activeCount }}</span>
        </div>
    </div>
    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('locations.manage'))
    <a href="{{ route('locations.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nueva ubicación
    </a>
    @endif
</div>

<div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
    @if($tree->isEmpty())
        <div class="text-center py-12 text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
            <div class="text-sm font-medium text-slate-600">No hay ubicaciones</div>
            <div class="text-xs mt-1">Crea la primera para empezar</div>
        </div>
    @else
        <ul class="space-y-1">
            @foreach($tree as $node)
                @include('locations.partials.tree_node', ['node' => $node, 'depth' => 0])
            @endforeach
        </ul>
    @endif
</div>
@endsection
