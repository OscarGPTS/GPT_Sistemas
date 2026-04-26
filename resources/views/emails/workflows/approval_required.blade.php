<x-emails.layout
    :subject="'Aprobación requerida · Solicitud #'.$instance->id"
    title="Tu aprobación es requerida"
    :greeting="'Hola '.$recipient->name.','"
    intro="Una solicitud está pendiente de tu aprobación en el flujo indicado. Por favor revísala cuanto antes para no demorar el proceso."
    :actionUrl="$url"
    actionText="Revisar y decidir"
    accent="#d97706"
    badge="Acción requerida"
>
    <x-emails.details :rows="[
        'Solicitud' => '<b style=\'font-family:monospace;\'>#'.$instance->id.'</b>',
        'Flujo' => e($instance->workflow?->name ?? '—'),
        'Solicitante' => e($instance->initiator?->name.' ('.($instance->initiator?->department ?? '—').')'),
        'Paso actual' => '<b>'.e($step->name).'</b>',
        'Fecha' => e($instance->created_at?->format('d/m/Y H:i')),
    ]" />

    @if($step->instructions)
        <p style="margin:16px 0 8px;color:#0f172a;font-size:14px;font-weight:600;">📝 Instrucciones del paso:</p>
        <div style="background:#fffbeb;border-left:3px solid #d97706;padding:12px 16px;color:#92400e;font-size:14px;border-radius:4px;">{{ $step->instructions }}</div>
    @endif

    @if($instance->payload && is_array($instance->payload))
        <p style="margin:20px 0 8px;color:#0f172a;font-size:14px;font-weight:600;">Datos de la solicitud:</p>
        <x-emails.details :rows="collect($instance->payload)->mapWithKeys(fn($v, $k) => [ucfirst(str_replace('_', ' ', $k)) => e(is_array($v) ? json_encode($v) : $v)])->all()" />
    @endif

    <x-emails.info-box tone="info">
        💡 Puedes aprobar o rechazar directamente desde el sistema usando el botón de arriba. Si rechazas, se recomienda agregar un comentario con el motivo.
    </x-emails.info-box>
</x-emails.layout>
