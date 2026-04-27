<x-emails.layout
    :subject="'Nueva solicitud de equipo '.$request->code"
    :title="'Nueva solicitud de equipo: '.$request->title"
    :greeting="'Hola '.$recipient->name.','"
    intro="Un colaborador acaba de enviar una solicitud de equipo a aprobación. Revisa los detalles a continuación."
    :actionUrl="$url"
    actionText="Ver solicitud"
    accent="#4f46e5"
    badge="Nueva solicitud"
>
    <x-emails.details :rows="[
        'Código' => '<b style=\'font-family:monospace;\'>'.$request->code.'</b>',
        'Título' => e($request->title),
        'Solicitante' => e($request->requester?->name ?? '—'),
        'Área' => e($request->requester?->department ?? '—'),
        'Prioridad' => '<span style=\'text-transform:capitalize;\'>'.e(['urgent'=>'Urgente','high'=>'Alta','medium'=>'Media','low'=>'Baja'][$request->priority] ?? $request->priority).'</span>',
        'Items solicitados' => e($request->items->count()),
        'Total estimado' => '$ '.number_format($request->totalEstimated(), 2),
    ]" />

    @if($request->justification)
        <p style="margin:16px 0 8px;color:#0f172a;font-size:14px;font-weight:600;">Justificación:</p>
        <div style="background:#f8fafc;border-left:3px solid #4f46e5;padding:12px 16px;color:#475569;font-size:14px;white-space:pre-wrap;border-radius:4px;">{{ $request->justification }}</div>
    @endif
</x-emails.layout>
