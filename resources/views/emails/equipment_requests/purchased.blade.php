<x-emails.layout
    :subject="'Solicitud '.$request->code.' comprada'"
    title="El equipo solicitado ya fue comprado"
    :greeting="'Hola '.$recipient->name.','"
    intro="Caja chica registró la compra del equipo solicitado. Estamos coordinando la entrega."
    :actionUrl="$url"
    actionText="Ver detalle"
    accent="#0891b2"
    badge="Comprado"
>
    <x-emails.details :rows="[
        'Código' => '<b style=\'font-family:monospace;\'>'.$request->code.'</b>',
        'Proveedor' => e($request->actual_supplier ?? '—'),
        'Fecha de compra' => e($request->purchased_at?->format('d/m/Y') ?? '—'),
        'Costo total' => '$ '.number_format((float) $request->actual_cost, 2),
        'Factura' => e($request->invoice_number ?? '—'),
    ]" />
</x-emails.layout>
