@extends('layouts.app')
@section('title', 'Notificaciones')
@section('page-title', 'Bandeja de notificaciones')
@section('page-subtitle', $unreadCount.' sin leer · '.$totalCount.' totales')

@section('content')
@php
    $iconMap = [
        'ticket'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/>',
        'asset'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25"/>',
        'wrench'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03"/>',
        'check'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'x'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>',
        'shield'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.249-8.25-3.285z"/>',
        'comment' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>',
        'info'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>',
    ];
    $iconColor = [
        'ticket.created' => 'bg-rose-100 text-rose-600',
        'ticket.assigned' => 'bg-amber-100 text-amber-600',
        'ticket.status_changed' => 'bg-blue-100 text-blue-600',
        'ticket.commented' => 'bg-violet-100 text-violet-600',
        'asset.assigned' => 'bg-emerald-100 text-emerald-600',
        'asset.released' => 'bg-slate-100 text-slate-600',
        'maintenance.due' => 'bg-amber-100 text-amber-600',
        'maintenance.overdue' => 'bg-red-100 text-red-600',
        'workflow.approval_required' => 'bg-orange-100 text-orange-600',
        'workflow.decided' => 'bg-brand-100 text-brand-600',
        'asset.warranty_expiring' => 'bg-amber-100 text-amber-600',
    ];
@endphp

<!-- Filter + actions -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="inline-flex bg-white border border-slate-200 rounded-lg p-1 shadow-card">
        <a href="{{ route('notifications.index') }}"
           class="px-4 py-1.5 rounded-md text-sm font-medium transition
                  {{ $filter === 'all' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
            Todas ({{ $totalCount }})
        </a>
        <a href="{{ route('notifications.index') }}?filter=unread"
           class="px-4 py-1.5 rounded-md text-sm font-medium transition
                  {{ $filter === 'unread' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
            Sin leer ({{ $unreadCount }})
        </a>
    </div>
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.markAllRead') }}">
            @csrf
            <button class="inline-flex items-center gap-2 text-sm text-brand-600 hover:text-brand-700 font-medium">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Marcar todas como leídas
            </button>
        </form>
    @endif
</div>

<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
    @forelse($notifications as $n)
        @php
            $type = $n->data['type'] ?? 'info';
            $iconKey = $n->data['icon'] ?? 'info';
            $colorCls = $iconColor[$type] ?? 'bg-slate-100 text-slate-600';
            $unread = $n->read_at === null;
        @endphp
        <div class="flex items-start gap-4 p-5 border-b border-slate-100 last:border-0 hover:bg-slate-50 transition {{ $unread ? 'bg-brand-50/30' : '' }}">
            @if($unread)
                <span class="w-2 h-2 bg-brand-500 rounded-full mt-2 flex-shrink-0" title="Sin leer"></span>
            @else
                <span class="w-2 h-2 flex-shrink-0"></span>
            @endif
            <div class="w-10 h-10 rounded-lg {{ $colorCls }} flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!! $iconMap[$iconKey] ?? $iconMap['info'] !!}</svg>
            </div>
            <div class="flex-1 min-w-0">
                <a href="{{ route('notifications.open', $n->id) }}" class="block">
                    <div class="font-semibold text-slate-900 text-sm">{{ $n->data['title'] ?? 'Notificación' }}</div>
                    <div class="text-sm text-slate-600 mt-0.5">{{ $n->data['message'] ?? '' }}</div>
                    <div class="text-xs text-slate-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
                </a>
            </div>
            <form method="POST" action="{{ route('notifications.destroy', $n->id) }}" class="flex-shrink-0">
                @csrf @method('DELETE')
                <button title="Eliminar" class="text-slate-300 hover:text-red-500 transition p-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                </button>
            </form>
        </div>
    @empty
        <div class="p-16 text-center">
            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
            </div>
            <div class="font-medium text-slate-800">Sin notificaciones</div>
            <div class="text-sm text-slate-500 mt-1">Te avisaremos aquí de actualizaciones importantes</div>
        </div>
    @endforelse

    @if($notifications->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
