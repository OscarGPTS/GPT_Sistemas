@extends('layouts.app')
@section('title', 'Presentaciones a video')
@section('page-title', 'Presentaciones a video')
@section('page-subtitle', 'Convierte tus presentaciones en video estático')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="text-sm text-slate-500">
        <span class="font-semibold text-slate-800">{{ $presentations->total() }}</span> conversión(es)
    </div>
    <a href="{{ route('presentation_videos.create') }}"
       class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Nueva conversión
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @forelse($presentations as $p)
        <div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden hover:shadow-md transition">
            <a href="{{ route('presentation_videos.show', $p) }}" class="block">
                <div class="relative aspect-video bg-slate-100 overflow-hidden">
                    @if($p->isCompleted())
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <svg class="w-16 h-16 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-emerald-700 text-sm font-medium mt-2">{{ $p->slide_count }} slide(s)</span>
                            <span class="text-emerald-600 text-xs mt-1">
                                {{ $p->slide_count * 15 }}s total
                            </span>
                        </div>
                    @elseif($p->isFailed())
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <svg class="w-16 h-16 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                            <span class="text-rose-600 text-sm font-medium mt-2">Error</span>
                        </div>
                    @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-brand-400 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
                            </svg>
                            <span class="text-slate-600 text-sm font-medium mt-2">{{ $p->statusLabel() }}</span>
                        </div>
                    @endif
                    <div class="absolute top-2 right-2">
                        <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full border {{ $p->statusColor() }}">
                            {{ $p->statusLabel() }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    <div class="font-medium text-slate-900 truncate text-sm">{{ $p->original_name }}</div>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-xs text-slate-500">{{ $p->created_at->diffForHumans() }}</span>
                        @if($p->slide_count)
                            <span class="text-[11px] text-slate-500">{{ $p->slide_count }} slides</span>
                        @endif
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-span-full bg-white rounded-xl shadow-card border border-slate-200 p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
            </svg>
            <div class="text-sm font-medium text-slate-600">Sin conversiones</div>
            <div class="text-xs text-slate-500 mt-1">Sube una presentación PowerPoint para convertirla en video.</div>
            <a href="{{ route('presentation_videos.create') }}" class="inline-block mt-4 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                Subir presentación
            </a>
        </div>
    @endforelse
</div>

@if($presentations->hasPages())
    <div class="mt-5">{{ $presentations->links() }}</div>
@endif
@endsection
