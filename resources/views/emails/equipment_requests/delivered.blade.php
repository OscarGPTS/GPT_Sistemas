<x-emails.layout
    :subject="'Solicitud '.$request->code.' entregada'"
    title="Equipo entregado"
    :greeting="'Hola '.$recipient->name.','"
    intro="El equipo solicitado ya fue entregado. Si es inventariable, se generó la asignación correspondiente automáticamente."
    :actionUrl="$url"
    actionText="Ver detalle"
    accent="#059669"
    badge="Entregado"
>
    <x-emails.details :rows="[
        'Código' => '<b style=\'font-family:monospace;\'>'.$request->code.'</b>',
        'Recibió' => e($request->deliveredTo?->name ?? '—'),
        'Entregó' => e($request->deliveredBy?->name ?? '—'),
        'Fecha' => e($request->delivered_at?->format('d/m/Y H:i') ?? '—'),
    ]" />

    @if($request->delivery_notes)
        <p style="margin:16px 0 8px;color:#0f172a;font-size:14px;font-weight:600;">Notas de entrega:</p>
        <div style="background:#f8fafc;border-left:3px solid #059669;padding:12px 16px;color:#475569;font-size:14px;border-radius:4px;">{{ $request->delivery_notes }}</div>
    @endif
</x-emails.layout>
