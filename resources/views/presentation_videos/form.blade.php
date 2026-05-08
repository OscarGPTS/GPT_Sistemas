@extends('layouts.app')
@section('title', 'Subir presentación')
@section('page-title', 'Nueva conversión')
@section('page-subtitle', 'Sube un archivo PowerPoint para convertir a video')

@section('content')
@php
    $input = 'border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 w-full';
    $label = 'block text-sm font-medium text-slate-700 mb-1';
@endphp

<div class="max-w-xl">
    <div class="bg-white rounded-xl shadow-card border border-slate-200 p-6">
        <form method="POST" action="{{ route('presentation_videos.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-5">
                <label for="presentation" class="{{ $label }}">Archivo de presentación</label>
                <input type="file" name="presentation" id="presentation"
                       accept=".ppt,.pptx"
                       class="{{ $input }}"
                       required>
                <p class="text-xs text-slate-500 mt-1.5">Formatos aceptados: .ppt, .pptx (máx. 100 MB)</p>
                @error('presentation')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-5">
                <h3 class="text-sm font-semibold text-slate-800 mb-2">Detalles de la conversión</h3>
                <ul class="space-y-1.5 text-xs text-slate-600">
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-brand-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Resolución de salida: <strong class="text-slate-800">1920 × 1080</strong> (Full HD)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-brand-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Duración por diapositiva: <strong class="text-slate-800">15 segundos</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-brand-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Sin animaciones · Sin audio · Imagen estática</span>
                    </li>
                </ul>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-6 py-2.5 rounded-lg text-sm font-medium shadow-sm transition">
                    Iniciar conversión
                </button>
                <a href="{{ route('presentation_videos.index') }}"
                   class="bg-white border border-slate-300 text-slate-700 px-4 py-2.5 rounded-lg hover:bg-slate-50 text-sm font-medium transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800">
        <b>Nota:</b> La conversión requiere que Microsoft PowerPoint esté instalado en el servidor para extraer las diapositivas. Si no se puede generar video, las diapositivas se empaquetarán como imágenes en un archivo ZIP.
    </div>
</div>
@endsection
