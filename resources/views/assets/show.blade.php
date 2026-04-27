@extends('layouts.app')
@section('title', 'Activo '.$asset->internal_code)
@section('page-title', $asset->internal_code)
@section('page-subtitle', $asset->brand.' '.$asset->model)

@section('content')
@php
    $statusCls = match($asset->status) {
        'available' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'assigned' => 'bg-amber-50 text-amber-700 border-amber-200',
        'in_maintenance' => 'bg-violet-50 text-violet-700 border-violet-200',
        'retired' => 'bg-slate-100 text-slate-600 border-slate-200',
        'lost' => 'bg-red-50 text-red-700 border-red-200',
        default => 'bg-slate-100 text-slate-600 border-slate-200',
    };
    $statusLabel = ['available'=>'Disponible','assigned'=>'Asignado','in_maintenance'=>'En mantenimiento','retired'=>'Baja','lost'=>'Extraviado'][$asset->status] ?? $asset->status;
@endphp

<!-- Header -->
<div class="bg-white rounded-xl shadow-card border border-slate-200 p-6 mb-5">
    <div class="flex flex-col lg:flex-row items-start gap-5">
        <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center flex-shrink-0">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"/></svg>
        </div>
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-slate-400 text-sm">{{ $asset->internal_code }}</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusCls }}">{{ $statusLabel }}</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $asset->brand }} {{ $asset->model }}</h1>
            <div class="text-sm text-slate-500 mt-1">{{ $asset->category?->name }} · {{ $asset->type }}</div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('assets.update'))
                <a href="{{ route('assets.edit', $asset) }}" class="inline-flex items-center gap-2 bg-white border border-slate-300 text-slate-700 px-3.5 py-2 rounded-lg hover:bg-slate-50 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                    Editar
                </a>
            @endif
            @if($asset->status === 'available' && (auth()->user()->isAdmin() || auth()->user()->hasPermission('assignments.manage')))
                <a href="{{ route('assignments.create') }}?asset_id={{ $asset->id }}"
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
                    Asignar
                </a>
            @endif
            @if($asset->currentAssignment && (auth()->user()->isAdmin() || auth()->user()->hasPermission('assignments.manage')))
                <form method="POST" action="{{ route('assignments.release', $asset) }}" onsubmit="return confirm('¿Liberar este activo?')">
                    @csrf
                    <button class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-500 text-white px-4 py-2 rounded-lg text-sm font-medium">Liberar</button>
                </form>
            @endif
            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('assets.delete'))
                <form method="POST" action="{{ route('assets.destroy', $asset) }}" onsubmit="return confirm('¿Dar de baja?')">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-medium">Dar de baja</button>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        <!-- Information card -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900">Información del equipo</h3>
            </div>
            <div class="p-5 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                @foreach([
                    ['Serie', $asset->serial_number ?? '—'],
                    ['Categoría', $asset->category?->name ?? '—'],
                    ['Condición', ucfirst($asset->condition)],
                    ['Ubicación', $asset->location ?? '—'],
                    ['Proveedor', $asset->supplier ?? '—'],
                    ['Compra', optional($asset->purchase_date)->format('d/m/Y') ?? '—'],
                    ['Costo', $asset->purchase_cost ? '$ '.number_format((float) $asset->purchase_cost, 2) : '—'],
                    ['Garantía', optional($asset->warranty_until)->format('d/m/Y') ?? '—'],
                    ['Registrado por', $asset->creator?->name ?? '—'],
                ] as $f)
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wide">{{ $f[0] }}</div>
                        <div class="text-slate-900 mt-0.5 font-medium">{{ $f[1] }}</div>
                    </div>
                @endforeach
            </div>
            @if($asset->description || $asset->notes || $asset->specs)
                <div class="border-t border-slate-100 p-5 space-y-3 text-sm">
                    @if($asset->description)<div><b class="text-slate-500 text-xs uppercase">Descripción</b><p class="text-slate-700 mt-1">{{ $asset->description }}</p></div>@endif
                    @if($asset->specs)
                        <div>
                            <b class="text-slate-500 text-xs uppercase">Especificaciones</b>
                            <dl class="mt-1 grid grid-cols-2 gap-2">
                                @foreach($asset->specs as $k => $v)
                                    <div class="flex gap-2"><dt class="text-slate-500 capitalize">{{ $k }}:</dt><dd class="text-slate-800">{{ $v }}</dd></div>
                                @endforeach
                            </dl>
                        </div>
                    @endif
                    @if($asset->notes)<div><b class="text-slate-500 text-xs uppercase">Notas</b><p class="text-slate-700 mt-1">{{ $asset->notes }}</p></div>@endif
                </div>
            @endif
        </div>

        <!-- Assignments history -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900">Historial de asignaciones</h3>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3">Usuario</th>
                        <th class="px-5 py-3">Desde</th>
                        <th class="px-5 py-3">Hasta</th>
                        <th class="px-5 py-3">Motivo</th>
                        <th class="px-5 py-3">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($asset->assignments->sortByDesc('assigned_at') as $as)
                        <tr>
                            <td class="px-5 py-3 text-slate-800">{{ $as->user?->name }}</td>
                            <td class="px-5 py-3 text-slate-600 text-xs">{{ $as->assigned_at?->format('d/m/Y') }}</td>
                            <td class="px-5 py-3 text-slate-600 text-xs">{{ $as->returned_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-500 text-xs">{{ $as->assignment_reason }}</td>
                            <td class="px-5 py-3">
                                @if($as->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-700"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activa</span>
                                @else
                                    <span class="text-xs text-slate-400">Cerrada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400 text-sm">Sin historial.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Maintenance -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-semibold text-slate-900">Mantenimientos</h3>
                @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('maintenance.manage'))
                <a href="{{ route('maintenance.records.create') }}?asset_id={{ $asset->id }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">+ Registrar</a>
                @endif
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse($asset->maintenanceRecords as $m)
                    <li class="px-5 py-3 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $m->status === 'completed' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }} flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-slate-800 text-sm">{{ $m->title }}</div>
                            <div class="text-xs text-slate-500">{{ $m->type === 'preventive' ? 'Preventivo' : 'Correctivo' }} · Programado: {{ $m->scheduled_date?->format('d/m/Y') }}</div>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full {{ $m->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">{{ ['scheduled'=>'Programado','in_progress'=>'En progreso','completed'=>'Completado','cancelled'=>'Cancelado'][$m->status] ?? $m->status }}</span>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-slate-400 text-sm">Sin mantenimientos registrados.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="space-y-5">
        <!-- Current -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Estado actual</h3>
            @if($asset->currentAssignment)
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center font-semibold">{{ strtoupper(substr($asset->currentAssignment->user?->name ?? '?', 0, 1)) }}</div>
                    <div class="min-w-0">
                        <div class="text-xs text-slate-500">Asignado a</div>
                        <div class="font-medium text-slate-900 truncate">{{ $asset->currentAssignment->user?->name }}</div>
                        <div class="text-xs text-slate-500">Desde {{ $asset->currentAssignment->assigned_at?->format('d/m/Y') }}</div>
                    </div>
                </div>
            @else
                <div class="text-sm text-slate-500">Sin asignación activa.</div>
            @endif
        </div>

        <!-- Related tickets -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900">Tickets relacionados</h3>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse($asset->tickets as $t)
                    <li class="px-5 py-3">
                        <a href="{{ route('tickets.show', $t) }}" class="block">
                            <div class="font-mono text-xs text-brand-600">{{ $t->code }}</div>
                            <div class="text-sm text-slate-800 truncate">{{ $t->subject }}</div>
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-slate-400 text-sm">Sin tickets relacionados.</li>
                @endforelse
            </ul>
        </div>

        @if($asset->isPrinter())
            <!-- Print users authorized for this printer -->
            <div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-semibold text-slate-900">Usuarios de impresión</h3>
                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('device_users.manage'))
                        <a href="{{ route('device_users.create') }}" class="text-xs text-brand-600 hover:underline">+ Asignar</a>
                    @endif
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse($asset->deviceUsers as $du)
                        <li class="px-5 py-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-xs font-semibold flex-shrink-0">{{ strtoupper(substr($du->full_name, 0, 1)) }}</div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('device_users.show', $du) }}" class="text-sm font-medium text-slate-800 hover:text-brand-600 truncate block">{{ $du->full_name }}</a>
                                <div class="text-xs text-slate-500">{{ $du->location?->name ?? '—' }}</div>
                            </div>
                            @if($du->isVisibleTo(auth()->user()))
                                <span class="font-mono text-xs bg-amber-50 px-2 py-0.5 rounded">{{ $du->print_code }}</span>
                            @else
                                <span class="font-mono text-xs text-slate-400">{{ $du->maskedPrintCode() }}</span>
                            @endif
                        </li>
                    @empty
                        <li class="px-5 py-6 text-center text-slate-400 text-sm">Sin usuarios autorizados (acceso abierto).</li>
                    @endforelse
                </ul>
            </div>
        @endif
    </div>
</div>
@endsection
