@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Hola, ' . auth()->user()->name . '. Este es el resumen de tu operación.')

@section('content')
@php
    $u = auth()->user();
    $cards = [
        ['label' => 'Activos totales', 'value' => $stats['assets_total'], 'link' => route('assets.index'),
         'color' => 'from-blue-500 to-indigo-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>'],
        ['label' => 'Disponibles', 'value' => $stats['assets_available'], 'link' => route('assets.index').'?status=available',
         'color' => 'from-emerald-500 to-teal-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['label' => 'Asignados', 'value' => $stats['assets_assigned'], 'link' => route('assets.index').'?status=assigned',
         'color' => 'from-amber-500 to-orange-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/>'],
        ['label' => 'En mantenimiento', 'value' => $stats['assets_maintenance'], 'link' => route('assets.index').'?status=in_maintenance',
         'color' => 'from-violet-500 to-purple-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63"/>'],
    ];
    $secondary = [
        ['label' => 'Tickets abiertos', 'value' => $stats['tickets_open'], 'link' => route('tickets.index').'?status=open',
         'color' => 'bg-rose-100 text-rose-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/>'],
        ['label' => 'Mis tickets', 'value' => $stats['tickets_mine'], 'link' => route('tickets.index'),
         'color' => 'bg-fuchsia-100 text-fuchsia-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>'],
        ['label' => 'Mantenim. próximos', 'value' => $stats['maintenance_due'], 'link' => route('maintenance.index'),
         'color' => 'bg-orange-100 text-orange-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['label' => 'Mis aprobaciones', 'value' => $stats['approvals_mine'], 'link' => route('workflows.my_approvals'),
         'color' => 'bg-emerald-100 text-emerald-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>'],
    ];
@endphp

<!-- Primary KPI cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    @foreach($cards as $c)
        <a href="{{ $c['link'] }}" class="group relative bg-white rounded-xl shadow-card border border-slate-200 p-5 hover:shadow-lg hover:-translate-y-0.5 transition overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-gradient-to-br {{ $c['color'] }} opacity-10 group-hover:opacity-20 transition"></div>
            <div class="flex items-start justify-between relative">
                <div>
                    <div class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $c['label'] }}</div>
                    <div class="text-3xl font-bold text-slate-900 mt-2">{{ $c['value'] }}</div>
                </div>
                <div class="w-11 h-11 rounded-lg bg-gradient-to-br {{ $c['color'] }} flex items-center justify-center text-white shadow">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!! $c['icon'] !!}</svg>
                </div>
            </div>
        </a>
    @endforeach
</div>

<!-- Secondary KPI cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach($secondary as $c)
        <a href="{{ $c['link'] }}" class="bg-white rounded-xl shadow-card border border-slate-200 p-4 hover:shadow-md transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg {{ $c['color'] }} flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!! $c['icon'] !!}</svg>
            </div>
            <div class="min-w-0">
                <div class="text-xs text-slate-500 truncate">{{ $c['label'] }}</div>
                <div class="text-xl font-semibold text-slate-900">{{ $c['value'] }}</div>
            </div>
        </a>
    @endforeach
</div>

<!-- Two-column: recent tickets + my assets -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <!-- Recent tickets -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-card border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-slate-900">Tickets recientes</h3>
                <p class="text-xs text-slate-500 mt-0.5">Últimas 5 incidencias/solicitudes</p>
            </div>
            <a href="{{ route('tickets.index') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium">Ver todos →</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentTickets as $t)
                <a href="{{ route('tickets.show', $t) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 transition">
                    @php
                        $pc = match($t->priority) {
                            'urgent' => 'bg-red-500',
                            'high' => 'bg-orange-500',
                            'medium' => 'bg-amber-500',
                            default => 'bg-slate-400',
                        };
                        $sc = match($t->status) {
                            'open' => 'bg-rose-100 text-rose-700',
                            'in_progress' => 'bg-amber-100 text-amber-700',
                            'on_hold' => 'bg-slate-100 text-slate-600',
                            'resolved' => 'bg-emerald-100 text-emerald-700',
                            'closed' => 'bg-slate-100 text-slate-500',
                            default => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <div class="w-1.5 h-10 rounded-full {{ $pc }}"></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs text-slate-400">{{ $t->code }}</span>
                            <span class="font-medium text-slate-800 text-sm truncate">{{ $t->subject }}</span>
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">{{ $t->requester?->name }} · {{ $t->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $sc }}">{{ $t->status }}</span>
                </a>
            @empty
                <div class="p-8 text-center">
                    <div class="text-sm text-slate-500">No hay tickets recientes</div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- My assets -->
    <div class="bg-white rounded-xl shadow-card border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-slate-900">Mis activos</h3>
                <p class="text-xs text-slate-500 mt-0.5">Equipos bajo mi responsabilidad</p>
            </div>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($myAssets as $a)
                <a href="{{ route('assets.show', $a->asset_id) }}" class="block px-5 py-3 hover:bg-slate-50">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-mono text-xs text-slate-500">{{ $a->asset->internal_code }}</div>
                            <div class="text-sm font-medium text-slate-800 truncate">{{ $a->asset->brand }} {{ $a->asset->model }}</div>
                            <div class="text-xs text-slate-500">Desde {{ $a->assigned_at?->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center">
                    <div class="text-sm text-slate-500">No tienes activos asignados</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Quick actions -->
<div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
    <h3 class="font-semibold text-slate-900 mb-4">Accesos rápidos</h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @php
            $actions = [
                ['Nuevo ticket', route('tickets.create'), 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z', 'bg-rose-50 text-rose-600'],
            ];
            if ($u->isAdmin() || $u->hasPermission('assets.create')) {
                $actions[] = ['Nuevo activo', route('assets.create'), 'M12 4.5v15m7.5-7.5h-15', 'bg-blue-50 text-blue-600'];
            }
            if ($u->isAdmin() || $u->hasPermission('assignments.manage')) {
                $actions[] = ['Asignar activo', route('assignments.create'), 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07', 'bg-amber-50 text-amber-600'];
            }
            if ($u->isAdmin() || $u->hasPermission('maintenance.manage')) {
                $actions[] = ['Mantenimiento', route('maintenance.records.create'), 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63', 'bg-violet-50 text-violet-600'];
            }
            if ($u->isAdmin() || $u->hasPermission('reports.view')) {
                $actions[] = ['Reportes', route('reports.index'), 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5', 'bg-emerald-50 text-emerald-600'];
            }
            if ($u->isAdmin() || $u->hasPermission('workflows.view')) {
                $actions[] = ['Flujos', route('workflows.index'), 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5', 'bg-slate-50 text-slate-600'];
            }
        @endphp
        @foreach($actions as $a)
            <a href="{{ $a[1] }}" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-slate-200 hover:border-brand-400 hover:shadow-sm transition">
                <div class="w-10 h-10 rounded-lg {{ $a[3] }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $a[2] }}"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-700 text-center">{{ $a[0] }}</span>
            </a>
        @endforeach
    </div>
</div>
@endsection
