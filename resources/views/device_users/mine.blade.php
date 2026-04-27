@extends('layouts.app')
@section('title', 'Mi código de impresión')
@section('page-title', 'Mi código de impresión')
@section('page-subtitle', 'Tu PIN para identificarte en las impresoras')

@section('content')
<div class="max-w-2xl mx-auto" x-data="{ visible: false }">
    @if($deviceUser)
        <div class="bg-white rounded-2xl shadow-card border border-slate-200 overflow-hidden mb-5">
            <!-- Hero card with the PIN -->
            <div class="bg-gradient-to-br from-brand-600 via-brand-700 to-slate-900 p-8 text-white relative overflow-hidden">
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 30%, white 1px, transparent 1px); background-size: 20px 20px;"></div>
                <div class="relative">
                    <div class="text-xs uppercase tracking-widest text-white/70 mb-2">Hola, {{ $user->name }}</div>
                    <div class="text-sm text-white/80 mb-6">Este es tu código personal de impresión:</div>
                    <div class="text-center mb-6">
                        <div class="font-mono font-bold tracking-wider transition-all"
                             :class="visible ? 'text-6xl' : 'text-6xl'"
                             x-text="visible ? '{{ $deviceUser->print_code }}' : '{{ $deviceUser->maskedPrintCode() }}'"></div>
                    </div>
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" @click="visible = !visible"
                                class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 backdrop-blur text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path x-show="!visible" stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path x-show="!visible" stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path x-show="visible" stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                            <span x-text="visible ? 'Ocultar' : 'Mostrar código'"></span>
                        </button>
                        <button type="button"
                                @click="navigator.clipboard.writeText('{{ $deviceUser->print_code }}'); $el.textContent = '¡Copiado!'; setTimeout(() => $el.textContent = 'Copiar', 1500)"
                                class="inline-flex items-center gap-2 bg-white text-brand-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-white/90 transition">
                            Copiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-slate-500 uppercase">Tu nombre</div>
                        <div class="font-medium mt-0.5">{{ $deviceUser->full_name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase">Buzón</div>
                        <div class="font-medium mt-0.5">{{ $deviceUser->mailbox ?? '—' }}</div>
                    </div>
                    @if($deviceUser->location)
                        <div class="col-span-2">
                            <div class="text-xs text-slate-500 uppercase mb-1">Ubicación</div>
                            <div class="flex flex-wrap items-center gap-1.5">
                                @foreach($deviceUser->location->path() as $node)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 text-sm">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $node->typeColor() }}"></span>
                                        {{ $node->name }}
                                    </span>
                                    @if(!$loop->last)<svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>@endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                @if($deviceUser->printers->isNotEmpty())
                    <div class="pt-4 border-t border-slate-100">
                        <div class="text-xs text-slate-500 uppercase mb-2">Tus impresoras autorizadas</div>
                        <ul class="space-y-1">
                            @foreach($deviceUser->printers as $printer)
                                <li class="flex items-center gap-2 text-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>
                                    <span class="text-slate-800">{{ $printer->brand }} {{ $printer->model }}</span>
                                    <span class="text-xs text-slate-400 font-mono">· {{ $printer->internal_code }}</span>
                                    @if($printer->location)<span class="text-xs text-slate-500">· {{ $printer->location }}</span>@endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="pt-4 border-t border-slate-100 bg-blue-50 -mx-6 -mb-6 px-6 py-3">
                        <div class="text-xs text-blue-800">
                            ℹ️ Tienes acceso libre a cualquier impresora corporativa.
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800">
            <b>📋 Cómo usar tu código:</b>
            <ol class="list-decimal pl-5 mt-1 space-y-0.5">
                <li>Acércate a la impresora</li>
                <li>En la pantalla, ingresa tu PIN de {{ strlen($deviceUser->print_code) }} dígitos</li>
                <li>Selecciona el documento que deseas imprimir</li>
            </ol>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18"/></svg>
            <div class="font-medium text-slate-700">Aún no tienes código de impresión asignado</div>
            <div class="text-sm text-slate-500 mt-1">Contacta al equipo de TI para que te asignen un código personal.</div>
            <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-2 mt-4 bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Solicitar código
            </a>
        </div>
    @endif
</div>
@endsection
