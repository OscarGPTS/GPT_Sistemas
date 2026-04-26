@extends('layouts.app')
@section('title', 'Ticket '.$ticket->code)
@section('page-title', $ticket->code)
@section('page-subtitle', $ticket->subject)

@section('content')
@php
    $pCls = match($ticket->priority) {
        'urgent' => 'bg-red-50 text-red-700 border-red-200',
        'high' => 'bg-orange-50 text-orange-700 border-orange-200',
        'medium' => 'bg-amber-50 text-amber-700 border-amber-200',
        default => 'bg-slate-50 text-slate-600 border-slate-200',
    };
    $pLabel = ['urgent'=>'Urgente','high'=>'Alta','medium'=>'Media','low'=>'Baja'][$ticket->priority] ?? $ticket->priority;
    $sCls = match($ticket->status) {
        'open' => 'bg-rose-50 text-rose-700 border-rose-200',
        'in_progress' => 'bg-amber-50 text-amber-700 border-amber-200',
        'on_hold' => 'bg-slate-100 text-slate-600 border-slate-200',
        'resolved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'closed' => 'bg-slate-100 text-slate-500 border-slate-200',
        default => 'bg-slate-50 text-slate-600 border-slate-200',
    };
    $sLabel = ['open'=>'Abierto','in_progress'=>'En progreso','on_hold'=>'En espera','resolved'=>'Resuelto','closed'=>'Cerrado','cancelled'=>'Cancelado'][$ticket->status] ?? $ticket->status;
@endphp

<!-- Header -->
<div class="bg-white rounded-xl shadow-card border border-slate-200 p-5 mb-5">
    <div class="flex flex-col md:flex-row items-start justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 flex-wrap mb-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $pCls }}">Prioridad {{ $pLabel }}</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $sCls }}">{{ $sLabel }}</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 capitalize">{{ ['incident'=>'Incidente','request'=>'Solicitud','maintenance'=>'Mantenimiento'][$ticket->type] ?? $ticket->type }}</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900">{{ $ticket->subject }}</h1>
            <div class="text-xs text-slate-500 mt-1">Solicitante: <b class="text-slate-700">{{ $ticket->requester?->name }}</b> · Creado {{ $ticket->created_at?->diffForHumans() }}</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        <!-- Description -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Descripción</h3>
            <div class="text-sm text-slate-700 whitespace-pre-wrap">{{ $ticket->description }}</div>
            @if($ticket->attachments->count())
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <div class="text-xs text-slate-500 mb-2 uppercase font-semibold">Adjuntos</div>
                    <ul class="space-y-1">
                        @foreach($ticket->attachments as $att)
                            <li>
                                <a href="{{ route('tickets.attachment', $att->id) }}" class="inline-flex items-center gap-2 text-sm text-brand-600 hover:text-brand-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                                    {{ $att->original_name }}
                                    <span class="text-xs text-slate-400">({{ number_format(($att->size ?? 0) / 1024, 0) }} KB)</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Comments -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-4">Conversación</h3>
            <div class="space-y-3 mb-5">
                @foreach($ticket->comments as $c)
                    <div class="flex gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-xs font-semibold flex-shrink-0">{{ strtoupper(substr($c->user?->name ?? '?', 0, 1)) }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="{{ $c->is_internal ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-200' }} border rounded-lg p-3">
                                <div class="flex justify-between text-xs text-slate-500 mb-1">
                                    <span><b class="text-slate-700">{{ $c->user?->name }}</b>
                                        @if($c->is_internal)<span class="text-amber-700 text-[10px] bg-amber-100 px-1.5 py-0.5 rounded ml-1">INTERNO</span>@endif
                                    </span>
                                    <span>{{ $c->created_at?->diffForHumans() }}</span>
                                </div>
                                <div class="text-sm text-slate-700 whitespace-pre-wrap">{{ $c->body }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
                @if($ticket->comments->isEmpty())
                    <div class="text-sm text-slate-400 text-center py-6">Aún no hay comentarios.</div>
                @endif
            </div>
            <form method="POST" action="{{ route('tickets.comment', $ticket) }}" class="space-y-2">
                @csrf
                <textarea name="body" rows="3" required class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100" placeholder="Escribe un comentario..."></textarea>
                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center text-xs text-slate-600">
                        <input type="checkbox" name="is_internal" value="1" class="mr-2 rounded text-brand-600 focus:ring-brand-500">
                        Comentario interno (solo TI/Admin)
                    </label>
                    <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">Agregar comentario</button>
                </div>
            </form>
        </div>

        <!-- History -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Historial de acciones</h3>
            <ul class="space-y-2 text-sm">
                @foreach($ticket->history as $h)
                    <li class="flex items-start gap-3 text-slate-600 text-xs">
                        <span class="w-2 h-2 rounded-full bg-slate-300 mt-1.5 flex-shrink-0"></span>
                        <span class="text-slate-400 font-mono">{{ $h->created_at?->format('d/m/Y H:i') }}</span>
                        <span><b class="text-slate-700">{{ $h->user?->name ?? 'Sistema' }}</b> — {{ $h->action }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="space-y-5">
        <!-- Management -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-4">Gestión</h3>
            <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Estado</label>
                    <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        @foreach(['open'=>'Abierto','in_progress'=>'En progreso','on_hold'=>'En espera','resolved'=>'Resuelto','closed'=>'Cerrado','cancelled'=>'Cancelado'] as $k=>$v)
                            <option value="{{ $k }}" @selected($ticket->status === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Prioridad</label>
                    <select name="priority" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        @foreach(['low'=>'Baja','medium'=>'Media','high'=>'Alta','urgent'=>'Urgente'] as $k=>$v)
                            <option value="{{ $k }}" @selected($ticket->priority === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Asignar a</label>
                    <select name="assignee_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">— Sin asignar —</option>
                        @foreach($itUsers as $u)
                            <option value="{{ $u->id }}" @selected($ticket->assignee_id === $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="w-full bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white py-2 rounded-lg text-sm font-medium shadow-sm">Actualizar</button>
            </form>
        </div>

        <!-- Details -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Detalles</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Activo</dt><dd>@if($ticket->asset)<a href="{{ route('assets.show', $ticket->asset_id) }}" class="text-brand-600 font-mono text-xs">{{ $ticket->asset->internal_code }}</a>@else<span class="text-slate-400">—</span>@endif</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Asignado</dt><dd class="text-slate-800">{{ $ticket->assignee?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Resuelto</dt><dd class="text-slate-800 text-xs">{{ $ticket->resolved_at?->format('d/m/Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Cerrado</dt><dd class="text-slate-800 text-xs">{{ $ticket->closed_at?->format('d/m/Y') ?? '—' }}</dd></div>
            </dl>
        </div>
    </div>
</div>
@endsection
