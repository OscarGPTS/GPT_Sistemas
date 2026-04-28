@extends('layouts.app')
@section('title', 'Cámaras de seguridad')
@section('page-title', 'Cámaras de seguridad')
@section('page-subtitle', 'Visualización en vivo y monitoreo')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="text-sm text-slate-500"><span class="font-semibold text-slate-800">{{ $cameras->total() }}</span> cámara(s)</div>
    <form method="GET" class="flex gap-2">
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar..." class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
        <button class="bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-4 py-2 text-sm">Filtrar</button>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
     x-data="{ refreshing: true }">
    @forelse($cameras as $asset)
        @php $cam = $asset->camera; @endphp
        <div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden hover:shadow-md transition">
            <a href="{{ route('cameras.show', $asset) }}" class="block">
                <!-- Live preview area -->
                <div class="relative aspect-video bg-slate-900 overflow-hidden">
                    @if($cam && $cam->mjpeg_url)
                        <!-- MJPEG: el navegador lo reproduce nativamente como stream de imágenes JPEG -->
                        <img src="{{ $cam->mjpeg_url }}"
                             alt="Live · {{ $asset->internal_code }}"
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-xs" style="display:none;">
                            <svg class="w-8 h-8 mb-1 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                            Sin señal
                        </div>
                        <div class="absolute top-2 left-2 inline-flex items-center gap-1.5 bg-red-500 text-white text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                            Live
                        </div>
                    @elseif($cam && $cam->snapshot_url)
                        <!-- Auto-refresh JPEG snapshot every 5s -->
                        <img x-data="{ url: '{{ $cam->snapshot_url }}?t='+Date.now() }"
                             :src="url"
                             x-init="setInterval(() => url = '{{ $cam->snapshot_url }}?t='+Date.now(), 5000)"
                             alt="Snapshot · {{ $asset->internal_code }}"
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-xs" style="display:none;">
                            <svg class="w-8 h-8 mb-1 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159"/></svg>
                            Sin señal
                        </div>
                        <div class="absolute top-2 left-2 inline-flex items-center gap-1.5 bg-blue-500 text-white text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded">
                            ⟳ Snapshot 5s
                        </div>
                    @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-500 text-xs">
                            <svg class="w-10 h-10 mb-2 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                            Sin stream configurado
                        </div>
                    @endif
                </div>

                <div class="p-4">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-mono text-xs text-slate-400">{{ $asset->internal_code }}</span>
                        @if($cam?->protocol)
                            <span class="text-[10px] uppercase font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-600">{{ $cam->protocol }}</span>
                        @endif
                    </div>
                    <div class="font-medium text-slate-900 truncate">{{ $asset->brand }} {{ $asset->model }}</div>
                    <div class="text-xs text-slate-500 truncate">📍 {{ $asset->location ?? '—' }}</div>
                    @if($cam)
                        <div class="flex items-center gap-2 mt-2 text-[11px] text-slate-500">
                            @if($cam->resolution)<span>{{ $cam->resolution }}</span>@endif
                            @if($cam->fps)<span>· {{ $cam->fps }}fps</span>@endif
                            @if($cam->has_audio)<span title="Audio">🔊</span>@endif
                            @if($cam->has_ptz)<span title="PTZ">🎯</span>@endif
                            @if($cam->has_motion_detection)<span title="Detección de movimiento">📡</span>@endif
                        </div>
                    @endif
                </div>
            </a>
        </div>
    @empty
        <div class="col-span-full bg-white rounded-xl shadow-card border border-slate-200 p-12 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
            <div class="text-sm font-medium text-slate-600">Sin cámaras registradas</div>
            <div class="text-xs text-slate-500 mt-1">Crea un activo de tipo "camera" o categoría "Cámaras" y luego configura sus URLs de stream.</div>
        </div>
    @endforelse
</div>

@if($cameras->hasPages())<div class="mt-5">{{ $cameras->links() }}</div>@endif

<div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-800">
    <b>💡 Sobre los protocolos:</b>
    <span class="block mt-1">• <b>MJPEG</b>: video en vivo, funciona directamente en el navegador (recomendado para LAN)</span>
    <span class="block">• <b>Snapshot</b>: imagen JPEG que se refresca cada 5 segundos</span>
    <span class="block">• <b>RTSP</b>: requiere transcodificación externa (no soportado nativamente en navegador)</span>
</div>
@endsection
