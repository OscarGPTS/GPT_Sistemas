@extends('layouts.app')
@section('title', 'Cámara '.$asset->internal_code)
@section('page-title', $asset->brand.' '.$asset->model)
@section('page-subtitle', 'Cámara · '.$asset->internal_code)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2">
        <!-- Live preview large -->
        <div class="bg-slate-900 rounded-xl overflow-hidden mb-4 relative aspect-video shadow-card">
            @if($camera && $camera->mjpeg_url)
                <img src="{{ $camera->mjpeg_url }}" alt="Live" class="w-full h-full object-contain"
                     onerror="this.style.display='none'; document.getElementById('no-stream').style.display='flex'">
                <div class="absolute top-3 left-3 inline-flex items-center gap-1.5 bg-red-500 text-white text-xs font-bold uppercase tracking-wide px-3 py-1 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                    En vivo · MJPEG
                </div>
            @elseif($camera && $camera->snapshot_url)
                <img x-data="{ url: '{{ $camera->snapshot_url }}?t='+Date.now(), refresh: 3 }"
                     :src="url"
                     x-init="setInterval(() => url = '{{ $camera->snapshot_url }}?t='+Date.now(), refresh*1000)"
                     alt="Snapshot" class="w-full h-full object-contain"
                     onerror="this.style.display='none'; document.getElementById('no-stream').style.display='flex'">
                <div class="absolute top-3 left-3 inline-flex items-center gap-1.5 bg-blue-500 text-white text-xs font-bold uppercase tracking-wide px-3 py-1 rounded-full">
                    ⟳ Refresh cada 3s
                </div>
            @else
                <div id="no-stream" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-sm">
                    <svg class="w-16 h-16 mb-3 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159"/></svg>
                    <div>Sin stream configurado</div>
                    @if($canManage)
                        <a href="{{ route('cameras.edit', $asset) }}" class="mt-3 bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg text-sm">Configurar URLs de stream</a>
                    @endif
                </div>
            @endif
            <div id="no-stream" style="display:none;" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-sm">
                <svg class="w-16 h-16 mb-3 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159"/></svg>
                <div>Sin señal · verifica la red y la URL del stream</div>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="flex flex-wrap gap-2 mb-5">
            @if($camera?->nvr_url)
                <a href="{{ $camera->nvr_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm">
                    🖥 Abrir NVR / DVR
                </a>
            @endif
            @if($camera?->access_id)
                <a href="{{ route('accesses.show', $camera->access_id) }}" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm">
                    🔐 Ver credenciales (vault)
                </a>
            @endif
            @if($canManage)
                <a href="{{ route('cameras.edit', $asset) }}" class="inline-flex items-center gap-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm">⚙️ Configurar streams</a>
            @endif
            <a href="{{ route('assets.show', $asset) }}" class="inline-flex items-center gap-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm">📦 Ficha del activo</a>
        </div>

        <!-- Stream URLs (admin only) -->
        @if($canManage && $camera)
            <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">URLs de stream configuradas</h3>
                <dl class="space-y-2 text-xs">
                    @if($camera->stream_url)
                        <div><dt class="text-slate-500 uppercase text-[10px]">RTSP / Stream principal</dt><dd class="font-mono break-all">{{ $camera->stream_url }}</dd></div>
                    @endif
                    @if($camera->mjpeg_url)
                        <div><dt class="text-slate-500 uppercase text-[10px]">MJPEG (visible aquí)</dt><dd class="font-mono break-all">{{ $camera->mjpeg_url }}</dd></div>
                    @endif
                    @if($camera->snapshot_url)
                        <div><dt class="text-slate-500 uppercase text-[10px]">Snapshot JPEG</dt><dd class="font-mono break-all">{{ $camera->snapshot_url }}</dd></div>
                    @endif
                    @if($camera->nvr_url)
                        <div><dt class="text-slate-500 uppercase text-[10px]">NVR / DVR Web UI</dt><dd class="font-mono break-all">{{ $camera->nvr_url }}</dd></div>
                    @endif
                </dl>
            </div>
        @endif
    </div>

    <div class="space-y-5">
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Información</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Marca</dt><dd>{{ $asset->brand ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Modelo</dt><dd>{{ $asset->model ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Serie</dt><dd class="font-mono text-xs">{{ $asset->serial_number ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Ubicación</dt><dd class="text-xs">{{ $asset->location ?? '—' }}</dd></div>
                @if($camera)
                    <div class="flex justify-between"><dt class="text-slate-500">Protocolo</dt><dd class="text-xs uppercase">{{ $camera->protocol }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Resolución</dt><dd class="text-xs">{{ $camera->resolution ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">FPS</dt><dd>{{ $camera->fps ?? '—' }}</dd></div>
                @endif
            </dl>
            @if($camera)
                <div class="flex flex-wrap gap-1.5 mt-4 pt-3 border-t border-slate-100">
                    @if($camera->has_audio)<span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded">🔊 Audio</span>@endif
                    @if($camera->has_motion_detection)<span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded">📡 Movimiento</span>@endif
                    @if($camera->has_ptz)<span class="text-xs bg-violet-50 text-violet-700 px-2 py-0.5 rounded">🎯 PTZ</span>@endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
