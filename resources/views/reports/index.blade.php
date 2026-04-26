@extends('layouts.app')
@section('title', 'Reportes')
@section('page-title', 'Reportes')
@section('page-subtitle', 'Indicadores clave y exportación de datos')

@section('content')
<!-- Export cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    @foreach([
        ['label'=>'Exportar activos','desc'=>'Inventario completo con estado, costo y garantía.','route'=>route('reports.export.assets'),'color'=>'from-blue-500 to-indigo-600'],
        ['label'=>'Exportar tickets','desc'=>'Historial completo con solicitante y asignación.','route'=>route('reports.export.tickets'),'color'=>'from-rose-500 to-orange-600'],
        ['label'=>'Exportar asignaciones','desc'=>'Trazabilidad de asignaciones y devoluciones.','route'=>route('reports.export.assignments'),'color'=>'from-emerald-500 to-teal-600'],
    ] as $card)
        <a href="{{ $card['route'] }}" class="group bg-white rounded-xl shadow-card border border-slate-200 p-5 hover:shadow-md hover:-translate-y-0.5 transition">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br {{ $card['color'] }} text-white flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                </div>
                <svg class="w-4 h-4 text-slate-400 group-hover:text-brand-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3"/></svg>
            </div>
            <div class="font-semibold text-slate-900">{{ $card['label'] }}</div>
            <div class="text-xs text-slate-500 mt-1">{{ $card['desc'] }}</div>
            <div class="mt-3 text-xs text-slate-400 font-mono">Formato: CSV (UTF-8)</div>
        </a>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    @php
        $statusColors = [
            'available'=>'bg-emerald-500', 'assigned'=>'bg-amber-500', 'in_maintenance'=>'bg-violet-500',
            'retired'=>'bg-slate-400', 'lost'=>'bg-red-500',
            'open'=>'bg-rose-500', 'in_progress'=>'bg-amber-500', 'on_hold'=>'bg-slate-400',
            'resolved'=>'bg-emerald-500', 'closed'=>'bg-slate-400', 'cancelled'=>'bg-slate-400',
            'low'=>'bg-slate-400', 'medium'=>'bg-amber-500', 'high'=>'bg-orange-500', 'urgent'=>'bg-red-500',
        ];
        $statusLabels = [
            'available'=>'Disponible','assigned'=>'Asignado','in_maintenance'=>'Mantenimiento',
            'retired'=>'Baja','lost'=>'Extraviado',
            'open'=>'Abierto','in_progress'=>'En progreso','on_hold'=>'En espera',
            'resolved'=>'Resuelto','closed'=>'Cerrado','cancelled'=>'Cancelado',
            'low'=>'Baja','medium'=>'Media','high'=>'Alta','urgent'=>'Urgente',
        ];
        $renderDistribution = function ($title, $data) use ($statusColors, $statusLabels) {
            $total = $data->sum() ?: 1;
            echo '<div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">';
            echo '<h3 class="font-semibold text-slate-900 mb-4">'.$title.'</h3>';
            echo '<div class="space-y-3">';
            foreach ($data as $key => $value) {
                $pct = round(($value / $total) * 100);
                $color = $statusColors[$key] ?? 'bg-slate-400';
                $label = $statusLabels[$key] ?? $key;
                echo '<div>';
                echo '<div class="flex justify-between text-xs mb-1"><span class="text-slate-600 capitalize">'.$label.'</span><span class="font-semibold text-slate-900">'.$value.' <span class="text-slate-400 font-normal">('.$pct.'%)</span></span></div>';
                echo '<div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden"><div class="h-full '.$color.' rounded-full transition-all" style="width:'.$pct.'%"></div></div>';
                echo '</div>';
            }
            echo '</div></div>';
        };
    @endphp

    {!! $renderDistribution('Activos por estado', $assetsByStatus) !!}
    {!! $renderDistribution('Tickets por estado', $ticketsByStatus) !!}
    {!! $renderDistribution('Tickets por prioridad', $ticketsByPriority) !!}

    <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
        <h3 class="font-semibold text-slate-900 mb-4">Activos por tipo</h3>
        <div class="grid grid-cols-2 gap-2">
            @foreach($assetsByType as $type => $total)
                <div class="flex items-center justify-between px-3 py-2 bg-slate-50 rounded-lg">
                    <span class="text-sm text-slate-600 capitalize">{{ $type }}</span>
                    <span class="font-semibold text-slate-900">{{ $total }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-900">Mantenimientos próximos (30 días)</h3>
        </div>
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Activo</th>
                    <th class="px-5 py-3">Título</th>
                    <th class="px-5 py-3">Fecha programada</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($maintenanceDue as $m)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-mono text-brand-600 text-xs">{{ $m->asset?->internal_code }}</td>
                        <td class="px-5 py-3 text-slate-800">{{ $m->title }}</td>
                        <td class="px-5 py-3 text-slate-600 text-sm">{{ $m->scheduled_date?->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
                @if($maintenanceDue->isEmpty())
                    <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400 text-sm">Sin mantenimientos programados.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
