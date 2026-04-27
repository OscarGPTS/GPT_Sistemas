@extends('layouts.app')
@section('title', 'Usuario impresión: '.$deviceUser->full_name)
@section('page-title', $deviceUser->full_name)
@section('page-subtitle', 'Usuario de impresión')

@section('content')
<div class="bg-white rounded-xl shadow-card border border-slate-200 p-5 mb-5 flex flex-wrap items-start justify-between gap-3">
    <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center font-bold flex-shrink-0">
            {{ strtoupper(substr($deviceUser->full_name, 0, 2)) }}
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap mb-1">
                @if($deviceUser->is_active)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs bg-slate-100 text-slate-600">Inactivo</span>
                @endif
                @if($deviceUser->employee_code)
                    <span class="font-mono text-xs text-slate-500">{{ $deviceUser->employee_code }}</span>
                @endif
            </div>
            <h1 class="text-xl font-bold text-slate-900">{{ $deviceUser->full_name }}</h1>
            <div class="text-sm text-slate-500 mt-0.5">{{ $deviceUser->email ?? '—' }}</div>
        </div>
    </div>
    <div class="flex flex-wrap gap-2">
        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('device_users.manage'))
            <a href="{{ route('device_users.edit', $deviceUser) }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-3.5 py-2 rounded-lg text-sm font-medium">Editar</a>
            <form method="POST" action="{{ route('device_users.regenerate', $deviceUser) }}" onsubmit="return confirm('¿Regenerar código?')">
                @csrf
                <button class="bg-amber-500 hover:bg-amber-600 text-white px-3.5 py-2 rounded-lg text-sm font-medium">🔄 Regenerar PIN</button>
            </form>
            <form method="POST" action="{{ route('device_users.toggle', $deviceUser) }}">
                @csrf
                <button class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-2 rounded-lg text-sm font-medium">{{ $deviceUser->is_active ? 'Desactivar' : 'Activar' }}</button>
            </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Información</h3>
            <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <dt class="text-xs text-slate-500 uppercase">Código impresión</dt>
                    <dd class="font-mono text-base font-bold text-slate-900 mt-0.5">
                        @if($canSeeCode)
                            <span class="bg-amber-50 border border-amber-200 px-2 py-1 rounded">{{ $deviceUser->print_code }}</span>
                        @else
                            <span class="text-slate-400">{{ $deviceUser->maskedPrintCode() }}</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500 uppercase">Buzón</dt>
                    <dd class="mt-0.5">{{ $deviceUser->mailbox ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500 uppercase">Usuario sistema</dt>
                    <dd class="mt-0.5">
                        @if($deviceUser->user)
                            <a href="{{ route('users.edit', $deviceUser->user) }}" class="text-brand-600 hover:underline">{{ $deviceUser->user->name }}</a>
                        @else <span class="text-slate-400">— Sin vincular —</span>
                        @endif
                    </dd>
                </div>
                <div class="col-span-2 md:col-span-3">
                    <dt class="text-xs text-slate-500 uppercase">Ubicación</dt>
                    <dd class="mt-0.5">
                        @if($deviceUser->location)
                            <div class="flex flex-wrap items-center gap-1.5 text-sm">
                                @foreach($deviceUser->location->path() as $node)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $node->typeColor() }}"></span>
                                        {{ $node->name }}
                                    </span>
                                    @if(!$loop->last)<svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>@endif
                                @endforeach
                            </div>
                        @else <span class="text-slate-400">—</span>
                        @endif
                    </dd>
                </div>
            </dl>
            @if($deviceUser->notes)
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <div class="text-xs text-slate-500 uppercase mb-1">Notas</div>
                    <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $deviceUser->notes }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Impresoras autorizadas</h3>
            @if($deviceUser->printers->isNotEmpty())
                <ul class="divide-y divide-slate-100">
                    @foreach($deviceUser->printers as $printer)
                        <li class="py-2 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('assets.show', $printer) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $printer->brand }} {{ $printer->model }}</a>
                                <div class="text-xs text-slate-500 font-mono">{{ $printer->internal_code }} · {{ $printer->location ?? '—' }}</div>
                            </div>
                            <span class="text-xs text-slate-400">desde {{ \Carbon\Carbon::parse($printer->pivot->granted_at)->format('d/m/Y') }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-sm text-slate-400 text-center py-6">Sin restricción · puede imprimir en cualquier impresora</div>
            @endif
        </div>
    </div>

    <div class="space-y-5">
        @if($canSeeCode)
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-5 text-center">
                <div class="text-xs text-amber-700 uppercase font-semibold mb-2">Tu código de impresión</div>
                <div class="text-3xl font-mono font-bold text-amber-900 tracking-wider mb-2">{{ $deviceUser->print_code }}</div>
                <div class="text-xs text-amber-700">Ingrésalo en la impresora para identificarte</div>
            </div>
        @else
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 text-center">
                <div class="text-xs text-slate-500 uppercase font-semibold mb-2">Código de impresión</div>
                <div class="text-3xl font-mono font-bold text-slate-400 tracking-wider mb-2">{{ $deviceUser->maskedPrintCode() }}</div>
                <div class="text-xs text-slate-500">Solo el propietario y TI pueden ver el código completo</div>
            </div>
        @endif
    </div>
</div>
@endsection
