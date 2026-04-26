@php
    $priorityColors = ['urgent'=>'#dc2626','high'=>'#ea580c','medium'=>'#d97706','low'=>'#64748b'];
    $priorityLabels = ['urgent'=>'Urgente','high'=>'Alta','medium'=>'Media','low'=>'Baja'];
    $accent = $priorityColors[$ticket->priority] ?? '#4f46e5';
@endphp
<x-emails.layout
    :subject="'Nuevo ticket '.$ticket->code"
    :title="'Nuevo ticket creado: '.$ticket->subject"
    :greeting="'Hola '.$recipient->name.','"
    intro="Se ha registrado un nuevo ticket en la mesa de servicio. Te compartimos los detalles para que puedas dar seguimiento."
    :actionUrl="$url"
    actionText="Ver ticket completo"
    :accent="$accent"
    badge="Nuevo Ticket"
>
    <x-emails.details :rows="[
        'Código' => '<b style=\'font-family:monospace;\'>'.$ticket->code.'</b>',
        'Asunto' => e($ticket->subject),
        'Tipo' => e(['incident'=>'Incidente','request'=>'Solicitud','maintenance'=>'Mantenimiento'][$ticket->type] ?? $ticket->type),
        'Prioridad' => '<span style=\'background:'.$accent.'15;color:'.$accent.';padding:2px 10px;border-radius:12px;font-size:12px;font-weight:600;\'>'.($priorityLabels[$ticket->priority] ?? $ticket->priority).'</span>',
        'Solicitante' => e($ticket->requester?->name ?? '—'),
        'Fecha' => e($ticket->created_at?->format('d/m/Y H:i')),
    ]" />

    <p style="margin:16px 0 8px;color:#0f172a;font-size:14px;font-weight:600;">Descripción:</p>
    <div style="background:#f8fafc;border-left:3px solid #cbd5e1;padding:12px 16px;color:#475569;font-size:14px;white-space:pre-wrap;border-radius:4px;">{{ $ticket->description }}</div>
</x-emails.layout>
