<x-emails.layout
    :subject="'Código de seguridad · '.$access->code"
    title="Tu código de seguridad"
    :greeting="'Hola '.$recipient->name.','"
    :intro="'Solicitaste revelar credenciales protegidas. Usa este código para confirmar tu identidad. El código expira en '.$minutes.' minutos y solo se puede usar una vez.'"
    accent="#dc2626"
    badge="Verificación"
>
    <div style="text-align:center; margin: 24px 0;">
        <div style="display:inline-block; background: #fef2f2; border: 2px dashed #fecaca; border-radius: 12px; padding: 24px 48px;">
            <div style="font-size: 11px; text-transform: uppercase; color: #991b1b; font-weight: 600; letter-spacing: 2px; margin-bottom: 6px;">Código de un solo uso</div>
            <div style="font-family: monospace; font-size: 36px; font-weight: 700; color: #7f1d1d; letter-spacing: 8px;">{{ $code }}</div>
        </div>
    </div>

    <x-emails.details :rows="[
        'Acceso solicitado' => '<b>'.e($access->code).'</b> · '.e($access->name),
        'Tipo' => e($access->type?->name ?? '—'),
        'Validez' => e($minutes).' minutos',
    ]" />

    <x-emails.info-box tone="warning">
        ⚠️ <b>Si no fuiste tú</b> quien solicitó este código, ignora este correo y notifica al equipo de seguridad.
        Nunca compartas este código con nadie ni lo respondas por correo.
    </x-emails.info-box>
</x-emails.layout>
