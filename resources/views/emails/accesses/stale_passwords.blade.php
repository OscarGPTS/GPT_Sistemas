<x-emails.layout
    subject="Contraseñas con rotación pendiente"
    title="Contraseñas que requieren rotación"
    :greeting="'Hola '.$recipient->name.','"
    :intro="'Detectamos '.$accesses->count().' contraseñas en el vault que no han sido rotadas en más de 90 días. Por política de seguridad, deben actualizarse.'"
    :actionUrl="$url"
    actionText="Ver listado completo"
    accent="#d97706"
    badge="Seguridad"
>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin:16px 0;">
        <thead>
            <tr>
                <th style="padding:10px 16px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;background:#f1f5f9;border-bottom:1px solid #e2e8f0;">Código</th>
                <th style="padding:10px 16px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;background:#f1f5f9;border-bottom:1px solid #e2e8f0;">Acceso</th>
                <th style="padding:10px 16px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;background:#f1f5f9;border-bottom:1px solid #e2e8f0;">Días</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accesses as $a)
                <tr>
                    <td style="padding:8px 16px;font-family:monospace;font-size:12px;color:#0f172a;border-bottom:1px solid #e2e8f0;">{{ $a->code }}</td>
                    <td style="padding:8px 16px;font-size:13px;color:#334155;border-bottom:1px solid #e2e8f0;">{{ $a->name }}</td>
                    <td style="padding:8px 16px;font-size:13px;color:#b45309;font-weight:600;border-bottom:1px solid #e2e8f0;">{{ $a->last_rotated_at ? round($a->last_rotated_at->diffInDays(now())) : '∞' }} días</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-emails.layout>
