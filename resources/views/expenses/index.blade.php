@extends('layouts.app')
@section('title', 'Gastos')
@section('page-title', 'Gastos TI · '.$monthDate->locale('es')->isoFormat('MMMM YYYY'))
@section('page-subtitle', 'Control de gastos recurrentes y variables')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex items-center gap-2">
        <label class="text-sm font-medium text-slate-700">Mes:</label>
        <input type="month" name="month" value="{{ $filters['month'] }}" class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
        <button class="bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-3 py-1.5 text-sm">Actualizar</button>
    </form>
    <div class="flex gap-2">
        <a href="{{ route('expenses.export', ['month' => $filters['month']]) }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-3.5 py-2 rounded-lg text-sm font-medium">📥 Exportar CSV</a>
        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('expense_categories.manage'))
        <a href="{{ route('expense_categories.index') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-3.5 py-2 rounded-lg text-sm font-medium">⚙️ Categorías</a>
        @endif
        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('expenses.manage'))
        <a href="{{ route('expenses.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Registrar gasto
        </a>
        @endif
    </div>
</div>

<!-- KPIs -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wide">Total del mes</div>
        <div class="text-2xl font-bold text-slate-900 mt-1">$ {{ number_format($monthlyTotal, 2) }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wide">Recurrentes</div>
        <div class="text-2xl font-bold text-blue-600 mt-1">$ {{ number_format($recurringTotal, 2) }}</div>
        <div class="text-xs text-slate-500">{{ $monthlyTotal > 0 ? round(($recurringTotal/$monthlyTotal)*100, 1) : 0 }}% del total</div>
    </div>
    <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wide">Variables</div>
        <div class="text-2xl font-bold text-amber-600 mt-1">$ {{ number_format($variableTotal, 2) }}</div>
        <div class="text-xs text-slate-500">{{ $monthlyTotal > 0 ? round(($variableTotal/$monthlyTotal)*100, 1) : 0 }}% del total</div>
    </div>
    <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
        <div class="text-xs text-slate-500 uppercase tracking-wide">Categorías activas</div>
        <div class="text-2xl font-bold text-slate-900 mt-1">{{ $monthlyByCategory->count() }} / {{ $categories->count() }}</div>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-card border border-slate-200 p-5">
        <h3 class="font-semibold text-slate-900 mb-3">Tendencia · últimos 6 meses</h3>
        <div style="position:relative; height:280px;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
        <h3 class="font-semibold text-slate-900 mb-3">Gasto por categoría · {{ $monthDate->locale('es')->isoFormat('MMM YYYY') }}</h3>
        @if($monthlyByCategory->count())
            <div style="position:relative; height:200px;">
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="mt-4 space-y-1.5">
                @foreach($monthlyByCategory->sortByDesc('total') as $row)
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $row['category']?->color ?? '#94a3b8' }}"></span>
                            {{ $row['category']?->name ?? '—' }}
                        </span>
                        <span class="font-mono font-medium">$ {{ number_format($row['total'], 0) }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-sm text-slate-400 text-center py-12">Sin gastos en este mes</div>
        @endif
    </div>
</div>

<!-- Filters & list -->
<form method="GET" class="bg-white rounded-xl shadow-card border border-slate-200 p-4 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <input type="hidden" name="month" value="{{ $filters['month'] }}">
        <div class="md:col-span-2 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar por concepto o proveedor..." class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg text-sm">
        </div>
        <select name="category_id" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Todas las categorías</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(($filters['category_id'] ?? '') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="type" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Recurrente y variable</option>
            <option value="recurring" @selected(($filters['type'] ?? '') === 'recurring')>Solo recurrentes</option>
            <option value="variable" @selected(($filters['type'] ?? '') === 'variable')>Solo variables</option>
        </select>
        <button class="bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-medium">Filtrar</button>
    </div>
</form>

<div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <th class="px-5 py-3">Código</th>
                <th class="px-5 py-3">Fecha</th>
                <th class="px-5 py-3">Categoría</th>
                <th class="px-5 py-3">Concepto</th>
                <th class="px-5 py-3">Proveedor</th>
                <th class="px-5 py-3">Tipo</th>
                <th class="px-5 py-3 text-right">Monto</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($expenses as $e)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-2.5 font-mono text-xs text-brand-600">{{ $e->code }}</td>
                    <td class="px-5 py-2.5 text-xs text-slate-500">{{ $e->expense_date?->format('d/m/Y') }}</td>
                    <td class="px-5 py-2.5">
                        <span class="inline-flex items-center gap-1.5 text-xs">
                            <span class="w-2 h-2 rounded-full" style="background: {{ $e->category?->color ?? '#94a3b8' }}"></span>
                            {{ $e->category?->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-5 py-2.5">
                        <div class="text-slate-800">{{ $e->concept }}</div>
                        @if($e->invoice_number)<div class="text-xs text-slate-400 font-mono">📄 {{ $e->invoice_number }}</div>@endif
                    </td>
                    <td class="px-5 py-2.5 text-slate-600 text-xs">{{ $e->supplier ?? '—' }}</td>
                    <td class="px-5 py-2.5"><span class="text-xs px-2 py-0.5 rounded-full {{ $e->type === 'recurring' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">{{ $e->type === 'recurring' ? 'Recurrente' : 'Variable' }}</span></td>
                    <td class="px-5 py-2.5 text-right font-mono font-semibold">$ {{ number_format((float) $e->amount, 2) }}</td>
                    <td class="px-5 py-2.5 text-right">
                        <a href="{{ route('expenses.show', $e) }}" class="text-brand-600 hover:underline text-xs">Ver</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-5 py-12 text-center text-slate-400 text-sm">Sin gastos en el periodo seleccionado.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($expenses->hasPages())<div class="px-5 py-3 border-t border-slate-100">{{ $expenses->links() }}</div>@endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function() {
    const trendData = @json($trend);
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trendData.map(d => d.label),
            datasets: [{
                label: 'Gasto mensual',
                data: trendData.map(d => d.value),
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(99,102,241,0.12)',
                tension: 0.35,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#4f46e5',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (v) => '$ ' + Number(v).toLocaleString('es-MX') }
                }
            }
        }
    });

    @if($monthlyByCategory->count())
    const catData = @json($monthlyByCategory->map(fn($r) => ['name' => $r['category']?->name ?? '—', 'total' => $r['total'], 'color' => $r['category']?->color ?? '#94a3b8']));
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: catData.map(d => d.name),
            datasets: [{
                data: catData.map(d => d.total),
                backgroundColor: catData.map(d => d.color),
                borderWidth: 2,
                borderColor: '#ffffff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            cutout: '65%',
        }
    });
    @endif
})();
</script>
@endsection
