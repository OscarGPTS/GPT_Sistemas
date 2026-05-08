@extends('layouts.app')
@section('title', 'Conversión · ' . $presentation->original_name)
@section('page-title', $presentation->original_name)
@section('page-subtitle', 'Conversión a video · ' . $presentation->created_at->isoFormat('D MMM YYYY'))

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        <!-- Status card -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-900">Estado de la conversión</h3>
                <span class="text-xs font-semibold uppercase tracking-wide px-3 py-1 rounded-full border {{ $presentation->statusColor() }}">
                    {{ $presentation->statusLabel() }}
                </span>
            </div>

            @if($presentation->isCompleted())
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <div class="font-semibold text-emerald-800 text-sm">Conversión completada</div>
                            <div class="text-xs text-emerald-700">{{ $presentation->slide_count }} diapositivas procesadas</div>
                        </div>
                    </div>
                </div>

                @php $isVideo = $presentation->video_path && str_ends_with($presentation->video_path, '.mp4'); @endphp

                @if($isVideo)
                    <div class="bg-slate-900 rounded-lg overflow-hidden mb-4 relative aspect-video shadow-card">
                        <video controls class="w-full h-full"
                               poster="">
                            <source src="{{ Storage::url($presentation->video_path) }}" type="video/mp4">
                            Tu navegador no soporta reproducción de video.
                        </video>
                    </div>
                @endif

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('presentation_videos.download', $presentation) }}"
                       class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-5 py-2.5 rounded-lg text-sm font-medium shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                        </svg>
                        Descargar {{ $isVideo ? 'video' : 'imágenes' }}
                    </a>
                    <a href="{{ route('presentation_videos.index') }}"
                       class="inline-flex items-center gap-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                        Volver a la lista
                    </a>
                </div>
            @elseif($presentation->isFailed())
                <div class="bg-rose-50 border border-rose-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-rose-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                        </svg>
                        <div>
                            <div class="font-semibold text-rose-800 text-sm">Error en la conversión</div>
                            <div class="text-xs text-rose-700 mt-1">{{ $presentation->error_message ?? 'Error desconocido' }}</div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('presentation_videos.create') }}"
                       class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-5 py-2.5 rounded-lg text-sm font-medium shadow-sm transition">
                        Intentar de nuevo
                    </a>
                    <a href="{{ route('presentation_videos.index') }}"
                       class="inline-flex items-center gap-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                        Volver
                    </a>
                </div>
            @else
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-brand-500 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
                        </svg>
                        <div>
                            <div class="font-semibold text-blue-800 text-sm">Procesando presentación</div>
                            <div class="text-xs text-blue-700">Extrayendo diapositivas y generando video...</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-5">
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Información</h3>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Archivo original</dt>
                    <dd class="text-slate-900 text-xs text-right max-w-[140px] truncate">{{ $presentation->original_name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Estado</dt>
                    <dd>
                        <span class="text-xs px-2 py-0.5 rounded-full border {{ $presentation->statusColor() }}">
                            {{ $presentation->statusLabel() }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Diapositivas</dt>
                    <dd class="text-slate-900">{{ $presentation->slide_count ?? '—' }}</dd>
                </div>
                @if($presentation->slide_count)
                <div class="flex justify-between">
                    <dt class="text-slate-500">Duración total</dt>
                    <dd class="text-slate-900">{{ $presentation->slide_count * 15 }} segundos</dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-slate-500">Resolución</dt>
                    <dd class="text-slate-900">1920 × 1080</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Creado</dt>
                    <dd class="text-slate-900 text-xs">{{ $presentation->created_at->isoFormat('D MMM YYYY, h:mm a') }}</dd>
                </div>
            </dl>
        </div>

        @if($presentation->video_path)
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Archivo generado</h3>
            @php
                $ext = pathinfo($presentation->video_path, PATHINFO_EXTENSION);
            @endphp
            <div class="text-xs">
                <span class="text-slate-500">Formato: </span>
                <span class="text-slate-900 font-mono uppercase">{{ $ext }}</span>
            </div>
            @php
                $size = Storage::exists($presentation->video_path) ? Storage::size($presentation->video_path) : null;
            @endphp
            @if($size)
            <div class="text-xs mt-1">
                <span class="text-slate-500">Tamaño: </span>
                <span class="text-slate-900">{{ number_format($size / 1024 / 1024, 1) }} MB</span>
            </div>
            @endif
        </div>
        @endif

        <!-- Delete -->
        <form method="POST" action="{{ route('presentation_videos.destroy', $presentation) }}"
              onsubmit="return confirm('¿Eliminar esta conversión?')"
              class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            @csrf
            @method('DELETE')
            <h3 class="font-semibold text-slate-900 mb-2">Eliminar conversión</h3>
            <p class="text-xs text-slate-500 mb-3">Se eliminará el archivo original y el video/imágenes generados.</p>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 px-4 py-2 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                </svg>
                Eliminar conversión
            </button>
        </form>
    </div>
</div>
@endsection
