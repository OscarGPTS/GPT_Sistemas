@extends('layouts.app')
@section('title', 'Configurar cámara · '.$asset->internal_code)
@section('page-title', 'Configurar cámara')
@section('page-subtitle', $asset->brand.' '.$asset->model.' · '.$asset->internal_code)

@section('content')
<form method="POST" action="{{ route('cameras.update', $asset) }}"
      class="bg-white rounded-xl shadow-card border border-slate-200 p-6 space-y-5 max-w-4xl">
    @csrf @method('PUT')
    @php
        $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
        $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
    @endphp

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Protocolo y URLs</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="{{ $label }}">Protocolo principal *</label>
                <select name="protocol" class="{{ $input }}">
                    @foreach(['rtsp'=>'RTSP','http'=>'HTTP','mjpeg'=>'MJPEG','hls'=>'HLS','onvif'=>'ONVIF','other'=>'Otro'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('protocol', $camera->protocol ?? 'rtsp') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Credenciales en vault</label>
                <select name="access_id" class="{{ $input }}">
                    <option value="">— Sin vincular —</option>
                    @foreach($accesses as $a)
                        <option value="{{ $a->id }}" @selected(old('access_id', $camera->access_id) == $a->id)>{{ $a->code }} · {{ $a->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 mt-1">Las credenciales viven en el vault, no aquí.</p>
            </div>
            <div class="md:col-span-2">
                <label class="{{ $label }}">URL principal de stream (RTSP/HLS)</label>
                <input name="stream_url" value="{{ old('stream_url', $camera->stream_url) }}" class="{{ $input }} font-mono" placeholder="rtsp://192.168.1.100:554/stream1">
                <p class="text-xs text-slate-500 mt-1">RTSP no se reproduce nativamente en navegadores. Usa MJPEG o snapshot abajo.</p>
            </div>
            <div>
                <label class="{{ $label }}">URL MJPEG <span class="text-emerald-600">⭐ recomendada</span></label>
                <input name="mjpeg_url" value="{{ old('mjpeg_url', $camera->mjpeg_url) }}" class="{{ $input }} font-mono" placeholder="http://192.168.1.100/video.mjpg">
                <p class="text-xs text-slate-500 mt-1">Stream JPEG concatenado, funciona directo en &lt;img&gt;.</p>
            </div>
            <div>
                <label class="{{ $label }}">URL Snapshot JPEG</label>
                <input name="snapshot_url" value="{{ old('snapshot_url', $camera->snapshot_url) }}" class="{{ $input }} font-mono" placeholder="http://192.168.1.100/cgi-bin/snapshot.cgi">
                <p class="text-xs text-slate-500 mt-1">Imagen estática que se refresca cada 5s.</p>
            </div>
            <div class="md:col-span-2">
                <label class="{{ $label }}">URL del NVR / DVR (web UI completa)</label>
                <input type="url" name="nvr_url" value="{{ old('nvr_url', $camera->nvr_url) }}" class="{{ $input }}" placeholder="http://nvr.local/login">
            </div>
        </div>
    </div>

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Especificaciones</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="{{ $label }}">Resolución</label>
                <input name="resolution" value="{{ old('resolution', $camera->resolution) }}" class="{{ $input }}" placeholder="1920x1080">
            </div>
            <div>
                <label class="{{ $label }}">FPS</label>
                <input type="number" name="fps" value="{{ old('fps', $camera->fps) }}" class="{{ $input }}" min="1" max="120">
            </div>
            <div class="space-y-1.5">
                <label class="inline-flex items-center text-sm">
                    <input type="checkbox" name="has_audio" value="1" @checked(old('has_audio', $camera->has_audio ?? false)) class="mr-2 rounded text-brand-600">
                    🔊 Soporta audio
                </label>
                <label class="inline-flex items-center text-sm">
                    <input type="checkbox" name="has_motion_detection" value="1" @checked(old('has_motion_detection', $camera->has_motion_detection ?? false)) class="mr-2 rounded text-brand-600">
                    📡 Detección de movimiento
                </label>
                <label class="inline-flex items-center text-sm">
                    <input type="checkbox" name="has_ptz" value="1" @checked(old('has_ptz', $camera->has_ptz ?? false)) class="mr-2 rounded text-brand-600">
                    🎯 PTZ
                </label>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <a href="{{ route('cameras.show', $asset) }}" class="px-5 py-2.5 text-slate-600 text-sm">Cancelar</a>
        <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium shadow-sm">Guardar configuración</button>
    </div>
</form>
@endsection
