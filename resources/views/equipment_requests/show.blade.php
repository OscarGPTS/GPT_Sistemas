@extends('layouts.app')
@section('title', 'Solicitud '.$request->code)
@section('page-title', $request->code)
@section('page-subtitle', $request->title)

@section('content')
@php
    // Determine which role-based panels should appear
    $canValidate = ($me->isAdmin() || $me->hasPermission('equipment_requests.validate')) && $request->status === 'approved';
    $canPurchase = ($me->isAdmin() || $me->hasPermission('equipment_requests.purchase')) && in_array($request->status, ['purchasing', 'approved']);
    $canDeliver = ($me->isAdmin() || $me->hasPermission('equipment_requests.deliver') || $me->hasPermission('equipment_requests.purchase')) && $request->status === 'purchased';
    $canSubmit = $request->status === 'draft' && ($me->id === $request->requester_id || $me->isAdmin());
    $canCancel = $request->isOpen() && ($me->id === $request->requester_id || $me->isAdmin());

    // Timeline steps
    $steps = [
        ['key' => 'draft',      'label' => 'Creada',      'when' => $request->created_at,     'icon' => 'M12 4.5v15m7.5-7.5h-15'],
        ['key' => 'in_review',  'label' => 'En aprobación','when' => $request->workflowInstance?->created_at ?? null, 'icon' => 'M9 12.75L11.25 15 15 9.75'],
        ['key' => 'approved',   'label' => 'Aprobada',    'when' => $request->workflowInstance?->status === 'approved' ? $request->workflowInstance->completed_at : null, 'icon' => 'M9 12.75L11.25 15 15 9.75'],
        ['key' => 'validated',  'label' => 'Validada TI', 'when' => $request->it_validated_at, 'icon' => 'M9 12.75L11.25 15 15 9.75'],
        ['key' => 'purchased',  'label' => 'Comprada',    'when' => $request->purchased_at,    'icon' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272'],
        ['key' => 'delivered',  'label' => 'Entregada',   'when' => $request->delivered_at,    'icon' => 'M4.5 12.75l6 6 9-13.5'],
    ];

    $isReached = function ($key) use ($request) {
        $order = ['draft','in_review','approved','validated','purchased','delivered'];
        $current = $request->status === 'rejected' ? 'in_review'
                : ($request->status === 'cancelled' ? 'in_review'
                : ($request->status === 'purchasing' ? 'validated'
                : $request->status));
        return array_search($key, $order) <= array_search($current, $order);
    };
@endphp

<!-- Header -->
<div class="bg-white rounded-xl shadow-card border border-slate-200 p-5 mb-5 flex flex-wrap items-start justify-between gap-3">
    <div>
        <div class="flex items-center gap-2 flex-wrap mb-1">
            <span class="font-mono text-xs text-slate-400">{{ $request->code }}</span>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $request->statusBadge() }}">{{ $request->statusLabel() }}</span>
            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 capitalize">Prioridad {{ ['urgent'=>'Urgente','high'=>'Alta','medium'=>'Media','low'=>'Baja'][$request->priority] ?? $request->priority }}</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $request->title }}</h1>
        <div class="text-sm text-slate-500 mt-1">{{ $request->requester?->name }} · {{ $request->created_at?->format('d/m/Y H:i') }}</div>
    </div>
    <div class="flex flex-wrap gap-2">
        @if($canSubmit)
            <form method="POST" action="{{ route('equipment_requests.submit', $request) }}">
                @csrf
                <button class="bg-gradient-to-r from-brand-600 to-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">Enviar a aprobación →</button>
            </form>
        @endif
        @if($canCancel)
            <form method="POST" action="{{ route('equipment_requests.cancel', $request) }}" onsubmit="return confirm('¿Cancelar solicitud?')">
                @csrf
                <button class="bg-white border border-slate-300 text-slate-700 hover:bg-red-50 hover:text-red-600 hover:border-red-300 px-4 py-2 rounded-lg text-sm font-medium">Cancelar</button>
            </form>
        @endif
    </div>
</div>

<!-- Timeline -->
<div class="bg-white rounded-xl shadow-card border border-slate-200 p-5 mb-5">
    <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-slate-900">Línea de tiempo</h3>
        @if($request->status === 'rejected')
            <span class="text-xs px-2 py-1 rounded-full bg-red-50 text-red-700 border border-red-200">Solicitud rechazada</span>
        @elseif($request->status === 'cancelled')
            <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">Solicitud cancelada</span>
        @endif
    </div>
    <div class="flex items-center gap-2 overflow-x-auto pb-2">
        @foreach($steps as $idx => $step)
            @php $reached = $isReached($step['key']); @endphp
            <div class="flex items-center gap-2 flex-shrink-0">
                <div class="flex flex-col items-center text-center" style="min-width: 110px;">
                    <div class="w-9 h-9 rounded-full {{ $reached ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center mb-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/></svg>
                    </div>
                    <div class="text-xs {{ $reached ? 'font-semibold text-slate-800' : 'text-slate-400' }}">{{ $step['label'] }}</div>
                    @if($step['when'])
                        <div class="text-[10px] text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($step['when'])->format('d/m H:i') }}</div>
                    @endif
                </div>
                @if(!$loop->last)
                    <div class="h-0.5 flex-1 {{ $reached ? 'bg-emerald-400' : 'bg-slate-200' }}" style="min-width: 32px;"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        <!-- Items -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-semibold text-slate-900">Equipos solicitados</h3>
                <div class="text-sm">Total estimado: <span class="font-bold text-slate-900">$ {{ number_format($request->totalEstimated(), 2) }}</span></div>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3">Tipo</th>
                        <th class="px-5 py-3">Descripción</th>
                        <th class="px-5 py-3">Modelo</th>
                        <th class="px-5 py-3 text-right">Cant.</th>
                        <th class="px-5 py-3 text-right">$ unit. est.</th>
                        <th class="px-5 py-3 text-right">$ unit. real</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($request->items as $item)
                        <tr>
                            <td class="px-5 py-2.5 capitalize">{{ $item->type }}</td>
                            <td class="px-5 py-2.5">
                                <div class="text-slate-800">{{ $item->description }}</div>
                                @if($item->purchase_link)
                                    <a href="{{ $item->purchase_link }}" target="_blank" class="text-xs text-brand-600 hover:underline">↗ Link de compra</a>
                                @endif
                            </td>
                            <td class="px-5 py-2.5 text-xs">
                                @if($item->approved_model)
                                    <div class="text-slate-800">{{ $item->approved_model }}</div>
                                    <div class="text-slate-400 line-through">{{ $item->suggested_model }}</div>
                                @else
                                    <span class="text-slate-600">{{ $item->suggested_model ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-2.5 text-right">{{ $item->quantity }}</td>
                            <td class="px-5 py-2.5 text-right font-mono text-xs">{{ $item->estimated_unit_cost ? '$ '.number_format((float) $item->estimated_unit_cost, 2) : '—' }}</td>
                            <td class="px-5 py-2.5 text-right font-mono text-xs {{ $item->actual_unit_cost ? 'text-emerald-700' : 'text-slate-400' }}">{{ $item->actual_unit_cost ? '$ '.number_format((float) $item->actual_unit_cost, 2) : '—' }}</td>
                            <td class="px-5 py-2.5">
                                @if($item->is_inventoriable)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">Inventariable</span>
                                @endif
                                @if($item->asset_id)
                                    <a href="{{ route('assets.show', $item->asset_id) }}" class="text-xs text-emerald-600 hover:underline">→ Activo</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Justification -->
        @if($request->justification)
            <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-2">Justificación</h3>
                <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $request->justification }}</p>
            </div>
        @endif

        <!-- IT Validation panel -->
        @if($canValidate)
            <div class="bg-white rounded-xl shadow-card border border-amber-300 ring-2 ring-amber-100 p-5">
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Validación TI</h3>
                        <p class="text-xs text-slate-500">Aprueba el modelo solicitado, propon proveedor y ajusta el costo estimado.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('equipment_requests.validate', $request) }}" class="space-y-4">
                    @csrf
                    <div class="space-y-2 mb-3">
                        @foreach($request->items as $item)
                            <div class="grid grid-cols-12 gap-2 items-center bg-slate-50 rounded-lg p-3 border border-slate-200">
                                <div class="col-span-12 md:col-span-3 text-sm">
                                    <div class="font-medium">{{ $item->type }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->suggested_model ?? '—' }}</div>
                                </div>
                                <div class="col-span-7 md:col-span-5">
                                    <input name="items[{{ $item->id }}][approved_model]" value="{{ $item->approved_model ?? $item->suggested_model }}" placeholder="Modelo aprobado" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                                </div>
                                <div class="col-span-5 md:col-span-3">
                                    <input name="items[{{ $item->id }}][estimated_unit_cost]" value="{{ $item->estimated_unit_cost }}" type="number" step="0.01" min="0" placeholder="$ unit." class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                                </div>
                                <div class="col-span-12 md:col-span-1">
                                    <label class="inline-flex items-center text-xs">
                                        <input type="checkbox" name="items[{{ $item->id }}][is_inventoriable]" value="1" @checked($item->is_inventoriable) class="mr-1 rounded text-brand-600">
                                        Inv.
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-slate-700">Proveedor sugerido</label>
                            <input name="preferred_supplier" value="{{ $request->preferred_supplier }}" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700">Costo total estimado</label>
                            <input name="estimated_cost" type="number" step="0.01" value="{{ $request->estimated_cost ?? $request->totalEstimated() }}" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700">Notas TI</label>
                        <textarea name="it_notes" rows="2" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">{{ $request->it_notes }}</textarea>
                    </div>
                    <button class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2 rounded-lg text-sm font-medium">Validar y enviar a Caja Chica →</button>
                </form>
            </div>
        @endif

        <!-- Purchase panel -->
        @if($canPurchase)
            <div class="bg-white rounded-xl shadow-card border border-violet-300 ring-2 ring-violet-100 p-5">
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Registro de compra · Caja Chica</h3>
                        <p class="text-xs text-slate-500">Completa los datos de la compra real y sube la factura.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('equipment_requests.purchase', $request) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="space-y-2 mb-3">
                        @foreach($request->items as $item)
                            <div class="grid grid-cols-12 gap-2 items-center bg-slate-50 rounded-lg p-3 border border-slate-200">
                                <div class="col-span-12 md:col-span-7 text-sm">
                                    <div class="font-medium">{{ $item->effectiveModel() }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->type }} · {{ $item->quantity }} unidad(es)</div>
                                </div>
                                <div class="col-span-12 md:col-span-5">
                                    <label class="text-xs text-slate-500">Costo unitario real</label>
                                    <input name="items[{{ $item->id }}][actual_unit_cost]" type="number" step="0.01" min="0" value="{{ $item->actual_unit_cost ?? $item->estimated_unit_cost }}" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="grid md:grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-slate-700">Proveedor real *</label>
                            <input name="actual_supplier" value="{{ $request->preferred_supplier }}" required class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700">Costo total real *</label>
                            <input name="actual_cost" type="number" step="0.01" min="0" required class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700">Fecha de compra *</label>
                            <input name="purchased_at" type="date" value="{{ now()->format('Y-m-d') }}" required class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700">No. Factura</label>
                            <input name="invoice_number" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-slate-700">Factura (PDF/XML)</label>
                            <input type="file" name="invoice" class="mt-1 w-full text-sm">
                        </div>
                    </div>
                    <button class="bg-violet-600 hover:bg-violet-700 text-white px-5 py-2 rounded-lg text-sm font-medium">Registrar compra →</button>
                </form>
            </div>
        @endif

        <!-- Delivery panel -->
        @if($canDeliver)
            <div class="bg-white rounded-xl shadow-card border border-emerald-300 ring-2 ring-emerald-100 p-5">
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Registro de entrega</h3>
                        <p class="text-xs text-slate-500">Confirma quién recibió el equipo y opcionalmente genera los activos automáticamente.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('equipment_requests.deliver', $request) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-slate-700">Recibido por</label>
                            <select name="delivered_to" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                                <option value="{{ $request->requester_id }}">{{ $request->requester?->name }} (solicitante)</option>
                                @foreach($users as $u)
                                    @continue($u->id === $request->requester_id)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700">Fecha de entrega *</label>
                            <input name="delivered_at" type="date" value="{{ now()->format('Y-m-d') }}" required class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-slate-700">Notas de entrega</label>
                            <textarea name="delivery_notes" rows="2" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"></textarea>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700">Evidencia (foto, ticket firmado)</label>
                            <input type="file" name="evidence" class="mt-1 w-full text-sm">
                        </div>
                        <div class="flex items-end">
                            <label class="inline-flex items-center text-sm text-slate-700">
                                <input type="checkbox" name="create_assets" value="1" checked class="mr-2 rounded text-brand-600">
                                Crear activos automáticamente para items inventariables
                            </label>
                        </div>
                    </div>
                    <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg text-sm font-medium">Confirmar entrega ✓</button>
                </form>
            </div>
        @endif

        <!-- Documents -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-slate-900">Documentos</h3>
                <span class="text-xs text-slate-500">{{ $request->documents->count() }} archivo(s)</span>
            </div>
            @if($request->documents->count())
                <ul class="divide-y divide-slate-100 mb-3">
                    @foreach($request->documents as $doc)
                        <li class="py-2 flex items-center gap-2">
                            @php
                                $kindCls = ['invoice'=>'bg-violet-50 text-violet-700','receipt'=>'bg-blue-50 text-blue-700','delivery_evidence'=>'bg-emerald-50 text-emerald-700','other'=>'bg-slate-100 text-slate-600'];
                            @endphp
                            <span class="text-[10px] uppercase font-semibold px-2 py-0.5 rounded {{ $kindCls[$doc->kind] ?? 'bg-slate-100' }}">{{ \App\Models\EquipmentRequestDocument::KINDS[$doc->kind] ?? $doc->kind }}</span>
                            <a href="{{ route('equipment_requests.documents.download', $doc->id) }}" class="text-sm text-brand-600 hover:underline flex-1">{{ $doc->original_name }}</a>
                            <span class="text-xs text-slate-400">{{ number_format(($doc->size ?? 0)/1024, 0) }} KB</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            <form method="POST" action="{{ route('equipment_requests.documents.upload', $request) }}" enctype="multipart/form-data" class="flex gap-2">
                @csrf
                <select name="kind" class="border border-slate-300 rounded px-2 py-1.5 text-sm">
                    @foreach(\App\Models\EquipmentRequestDocument::KINDS as $k => $label)
                        <option value="{{ $k }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input type="file" name="file" required class="flex-1 text-sm">
                <button class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded text-xs font-medium">+ Subir</button>
            </form>
        </div>
    </div>

    <div class="space-y-5">
        <!-- Summary card -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Resumen</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Items</dt><dd class="font-medium">{{ $request->items->count() }} ({{ $request->items->sum('quantity') }} u.)</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Total estimado</dt><dd class="font-mono text-sm">$ {{ number_format($request->totalEstimated(), 2) }}</dd></div>
                @if($request->actual_cost)
                <div class="flex justify-between"><dt class="text-slate-500">Total real</dt><dd class="font-mono text-sm font-bold text-emerald-700">$ {{ number_format((float) $request->actual_cost, 2) }}</dd></div>
                @endif
                @if($request->preferred_supplier)
                <div class="flex justify-between"><dt class="text-slate-500">Proveedor sugerido</dt><dd class="text-xs">{{ $request->preferred_supplier }}</dd></div>
                @endif
                @if($request->actual_supplier)
                <div class="flex justify-between"><dt class="text-slate-500">Proveedor real</dt><dd class="text-xs font-medium">{{ $request->actual_supplier }}</dd></div>
                @endif
                @if($request->invoice_number)
                <div class="flex justify-between"><dt class="text-slate-500">Factura</dt><dd class="font-mono text-xs">{{ $request->invoice_number }}</dd></div>
                @endif
                @if($request->project)
                <div class="flex justify-between"><dt class="text-slate-500">Proyecto</dt><dd class="text-xs"><a href="{{ route('projects.board', $request->project) }}" class="text-brand-600">{{ $request->project->name }}</a></dd></div>
                @endif
            </dl>
        </div>

        <!-- Workflow approval status -->
        @if($request->workflowInstance)
            <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">Aprobaciones</h3>
                <div class="space-y-2">
                    @foreach($request->workflowInstance->workflow->steps as $step)
                        @php
                            $approval = $request->workflowInstance->approvals->where('step_id', $step->id)->whereIn('decision', ['approved','rejected'])->first();
                            $isCurrent = $step->order === $request->workflowInstance->current_order && $request->workflowInstance->status === 'in_review';
                            $cls = $approval?->decision === 'approved' ? 'bg-emerald-500' : ($approval?->decision === 'rejected' ? 'bg-red-500' : ($isCurrent ? 'bg-amber-500 animate-pulse' : 'bg-slate-300'));
                        @endphp
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full {{ $cls }} text-white text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $step->order }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-slate-800">{{ $step->name }}</div>
                                @if($approval)
                                    <div class="text-xs text-slate-500">{{ $approval->approver?->name }} · {{ $approval->decided_at?->format('d/m H:i') }}</div>
                                    @if($approval->comment)
                                        <div class="text-xs text-slate-600 italic mt-0.5">"{{ $approval->comment }}"</div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('workflows.instances.show', $request->workflowInstance) }}" class="mt-3 block text-xs text-brand-600 hover:underline">Ver flujo completo →</a>
            </div>
        @endif

        @if($request->it_validator || $request->buyer || $request->deliveredBy)
            <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">Personas</h3>
                <dl class="space-y-2 text-sm">
                    @if($request->it_validator)
                        <div class="flex justify-between"><dt class="text-slate-500">Validó TI</dt><dd>{{ $request->itValidator->name }}</dd></div>
                    @endif
                    @if($request->buyer)
                        <div class="flex justify-between"><dt class="text-slate-500">Compró</dt><dd>{{ $request->buyer->name }}</dd></div>
                    @endif
                    @if($request->deliveredBy)
                        <div class="flex justify-between"><dt class="text-slate-500">Entregó</dt><dd>{{ $request->deliveredBy->name }}</dd></div>
                    @endif
                    @if($request->deliveredTo)
                        <div class="flex justify-between"><dt class="text-slate-500">Recibió</dt><dd>{{ $request->deliveredTo->name }}</dd></div>
                    @endif
                </dl>
            </div>
        @endif
    </div>
</div>
@endsection
