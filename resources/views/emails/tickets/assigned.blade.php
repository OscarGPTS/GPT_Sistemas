<x-emails.layout
    :subject="'Ticket '.$ticket->code.' asignado a ti'"
    title="Se te ha asignado un ticket"
    :greeting="'Hola '.$recipient->name.','"
    intro="Un ticket de la mesa de servicio fue asignado a ti para su atención. A continuación los detalles para que puedas comenzar."
    :actionUrl="$url"
    actionText="Atender ticket"
    accent="#4f46e5"
    badge="Asignado"
>
    <x-emails.details :rows="[
        'Código' => '<b style=\'font-family:monospace;\'>'.$ticket->code.'</b>',
        'Asunto' => e($ticket->subject),
        'Prioridad' => '<span style=\'text-transform:capitalize;\'>'.e(['urgent'=>'Urgente','high'=>'Alta','medium'=>'Media','low'=>'Baja'][$ticket->priority] ?? $ticket->priority).'</span>',
        'Solicitante' => e($ticket->requester?->name ?? '—'),
        'Área' => e($ticket->requester?->department ?? '—'),
    ]" />

    <p style="margin:16px 0 8px;color:#0f172a;font-size:14px;font-weight:600;">Descripción del caso:</p>
    <div style="background:#f8fafc;border-left:3px solid #4f46e5;padding:12px 16px;color:#475569;font-size:14px;white-space:pre-wrap;border-radius:4px;">{{ $ticket->description }}</div>
</x-emails.layout>
