@php
    $accent = match($toStatus) {
        'resolved', 'closed' => '#059669',
        'on_hold' => '#d97706',
        'cancelled' => '#64748b',
        default => '#4f46e5',
    };
    $badge = match($toStatus) {
        'resolved' => 'Resuelto',
        'closed' => 'Cerrado',
        'on_hold' => 'En espera',
        'cancelled' => 'Cancelado',
        'in_progress' => 'En progreso',
        default => 'Actualización',
    };
@endphp
<x-emails.layout
    :subject="'Tu ticket '.$ticket->code.' fue actualizado'"
    title="Tu ticket ha cambiado de estado"
    :greeting="'Hola '.$recipient->name.','"
    intro="El estado de tu ticket ha sido actualizado. Consulta el detalle de la actualización a continuación."
    :actionUrl="$url"
    actionText="Ver ticket"
    :accent="$accent"
    :badge="$badge"
>
    <x-emails.details :rows="[
        'Código' => '<b style=\'font-family:monospace;\'>'.$ticket->code.'</b>',
        'Asunto' => e($ticket->subject),
        'Estado anterior' => '<span style=\'color:#94a3b8;\'>'.e($fromLabel).'</span>',
        'Estado actual' => '<span style=\'background:'.$accent.'15;color:'.$accent.';padding:2px 10px;border-radius:12px;font-size:12px;font-weight:600;\'>'.e($toLabel).'</span>',
        'Asignado' => e($ticket->assignee?->name ?? '—'),
    ]" />

    @if(in_array($toStatus, ['resolved','closed']))
        <x-emails.info-box tone="success">
            ✅ Si el problema quedó resuelto, no es necesario hacer nada más. Si notas que el problema persiste o reaparece, contacta a soporte directamente.
        </x-emails.info-box>
    @endif
</x-emails.layout>
