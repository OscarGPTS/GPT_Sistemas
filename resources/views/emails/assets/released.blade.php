<x-emails.layout
    :subject="'Activo '.$asset->internal_code.' liberado'"
    title="Un activo a tu cargo fue liberado"
    :greeting="'Hola '.$recipient->name.','"
    intro="Te informamos que el siguiente activo ha sido liberado de tu asignación. Si no estás al tanto de esta devolución, contacta al equipo de TI."
    :actionUrl="$url"
    actionText="Ver detalle"
    accent="#64748b"
    badge="Devolución"
>
    <x-emails.details :rows="[
        'Código interno' => '<b style=\'font-family:monospace;\'>'.$asset->internal_code.'</b>',
        'Equipo' => e($asset->brand.' '.$asset->model),
        'Fecha de devolución' => e(now()->format('d/m/Y H:i')),
        'Motivo' => e($reason ?? 'No especificado'),
    ]" />
</x-emails.layout>
