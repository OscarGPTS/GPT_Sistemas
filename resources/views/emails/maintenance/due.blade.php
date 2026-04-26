@php
    $accent = $overdue ? '#dc2626' : '#d97706';
    $badge = $overdue ? 'Vencido' : 'Próximo';
@endphp
<x-emails.layout
    :subject="($overdue ? '[VENCIDO] ' : '').'Mantenimiento: '.$record->title"
    :title="$overdue ? 'Mantenimiento vencido requiere atención' : 'Mantenimiento próximo programado'"
    :greeting="'Hola '.$recipient->name.','"
    :intro="$overdue
        ? 'Tenemos un mantenimiento que debió realizarse pero sigue pendiente. Es importante que lo atiendas cuanto antes.'
        : 'Se acerca la fecha de un mantenimiento programado que tienes asignado. Te pedimos coordinar con el usuario del equipo para realizarlo en tiempo.'"
    :actionUrl="$url"
    :actionText="$overdue ? 'Atender ahora' : 'Ver programación'"
    :accent="$accent"
    :badge="$badge"
>
    <x-emails.details :rows="[
        'Activo' => '<b style=\'font-family:monospace;\'>'.$record->asset?->internal_code.'</b> · '.e($record->asset?->brand.' '.$record->asset?->model),
        'Tipo' => e($record->type === 'preventive' ? 'Preventivo' : 'Correctivo'),
        'Título' => e($record->title),
        'Fecha programada' => e($record->scheduled_date?->format('d/m/Y')),
        'Responsable' => e($record->responsible?->name ?? '—'),
    ]" />

    @if($record->description)
        <p style="margin:16px 0 8px;color:#0f172a;font-size:14px;font-weight:600;">Detalles:</p>
        <div style="background:#f8fafc;border-left:3px solid {{ $accent }};padding:12px 16px;color:#475569;font-size:14px;border-radius:4px;">{{ $record->description }}</div>
    @endif

    @if($overdue)
        <x-emails.info-box tone="danger">
            ⚠️ <b>Acción requerida:</b> Este mantenimiento está vencido. Por favor completa la intervención cuanto antes o actualiza su fecha programada.
        </x-emails.info-box>
    @endif
</x-emails.layout>
