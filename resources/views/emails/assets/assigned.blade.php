<x-emails.layout
    :subject="'Activo '.$asset->internal_code.' asignado a ti'"
    title="Se te ha asignado un activo"
    :greeting="'Hola '.$recipient->name.','"
    intro="El equipo de TI te ha asignado un nuevo activo. Te pedimos cuidar del equipo y reportar cualquier incidencia a la mesa de servicio."
    :actionUrl="$url"
    actionText="Ver detalle del activo"
    accent="#059669"
    badge="Entrega de equipo"
>
    <x-emails.details :rows="[
        'Código interno' => '<b style=\'font-family:monospace;\'>'.$asset->internal_code.'</b>',
        'Equipo' => e($asset->brand.' '.$asset->model),
        'Tipo' => e($asset->category?->name ?? $asset->type),
        'Número de serie' => e($asset->serial_number ?? '—'),
        'Ubicación' => e($assignment->assignment_location ?? $asset->location ?? '—'),
        'Condición de entrega' => e(ucfirst($assignment->condition_out ?? $asset->condition ?? '—')),
        'Fecha de asignación' => e($assignment->assigned_at?->format('d/m/Y')),
    ]" />

    @if($assignment->assignment_reason)
        <p style="margin:16px 0 8px;color:#0f172a;font-size:14px;font-weight:600;">Motivo:</p>
        <div style="background:#f8fafc;border-left:3px solid #059669;padding:12px 16px;color:#475569;font-size:14px;border-radius:4px;">{{ $assignment->assignment_reason }}</div>
    @endif

    <x-emails.info-box tone="info">
        📋 <b>Responsabilidades:</b> Cuidar y resguardar el equipo durante su uso. En caso de falla, robo o extravío reporta de inmediato al equipo de TI mediante un ticket en la mesa de servicio.
    </x-emails.info-box>
</x-emails.layout>
