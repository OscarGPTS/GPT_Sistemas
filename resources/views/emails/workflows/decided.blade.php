@php
    $approved = $decision === 'approved';
    $accent = $approved ? '#059669' : '#dc2626';
    $badge = $approved ? ($isFinal ? 'Aprobada' : 'Avance') : 'Rechazada';
@endphp
<x-emails.layout
    :subject="'Solicitud #'.$instance->id.' '.($approved ? 'aprobada' : 'rechazada')"
    :title="$isFinal
        ? ($approved ? 'Tu solicitud fue aprobada' : 'Tu solicitud fue rechazada')
        : 'Tu solicitud avanzó en el flujo'"
    :greeting="'Hola '.$recipient->name.','"
    :intro="$isFinal
        ? ($approved
            ? 'Nos complace informarte que tu solicitud completó todos los pasos de aprobación satisfactoriamente.'
            : 'Te informamos que tu solicitud fue rechazada en uno de los pasos del flujo de aprobación.')
        : ($approved
            ? 'Un aprobador autorizó el paso actual; la solicitud avanza al siguiente nivel.'
            : 'Un aprobador rechazó el paso actual del flujo.')"
    :actionUrl="$url"
    actionText="Ver detalle"
    :accent="$accent"
    :badge="$badge"
>
    <x-emails.details :rows="[
        'Solicitud' => '<b style=\'font-family:monospace;\'>#'.$instance->id.'</b>',
        'Flujo' => e($instance->workflow?->name ?? '—'),
        'Decidido por' => e($approver->name),
        'Decisión' => '<span style=\'background:'.$accent.'15;color:'.$accent.';padding:2px 10px;border-radius:12px;font-size:12px;font-weight:600;\'>'.($approved ? 'Aprobado' : 'Rechazado').'</span>',
        'Fecha' => e(now()->format('d/m/Y H:i')),
    ]" />

    @if($comment)
        <p style="margin:16px 0 8px;color:#0f172a;font-size:14px;font-weight:600;">Comentario del aprobador:</p>
        <div style="background:#f8fafc;border-left:3px solid {{ $accent }};padding:12px 16px;color:#475569;font-size:14px;border-radius:4px;font-style:italic;">"{{ $comment }}"</div>
    @endif

    @if($isFinal && $approved)
        <x-emails.info-box tone="success">
            🎉 <b>¡Listo!</b> Todos los pasos fueron aprobados. El equipo correspondiente procederá con la ejecución de tu solicitud.
        </x-emails.info-box>
    @elseif($isFinal && !$approved)
        <x-emails.info-box tone="danger">
            ⚠️ Si consideras que el rechazo no corresponde, puedes abrir un ticket en la mesa de servicio o contactar al aprobador directamente.
        </x-emails.info-box>
    @endif
</x-emails.layout>
