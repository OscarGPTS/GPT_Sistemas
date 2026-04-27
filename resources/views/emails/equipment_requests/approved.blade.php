<x-emails.layout
    :subject="'Solicitud '.$request->code.' aprobada'"
    title="Tu solicitud de equipo fue aprobada"
    :greeting="'Hola '.$recipient->name.','"
    intro="La solicitud completó el flujo de aprobación. El equipo de TI validará el modelo final y caja chica procederá con la compra."
    :actionUrl="$url"
    actionText="Ver detalle"
    accent="#059669"
    badge="Aprobada"
>
    <x-emails.details :rows="[
        'Código' => '<b style=\'font-family:monospace;\'>'.$request->code.'</b>',
        'Título' => e($request->title),
        'Estado actual' => '<span style=\'background:#ecfdf5;color:#065f46;padding:2px 10px;border-radius:12px;font-size:12px;font-weight:600;\'>'.$request->statusLabel().'</span>',
        'Total estimado' => '$ '.number_format($request->totalEstimated(), 2),
    ]" />
</x-emails.layout>
