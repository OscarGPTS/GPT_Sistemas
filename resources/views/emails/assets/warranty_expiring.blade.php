<x-emails.layout
    subject="Garantías próximas a vencer"
    title="Garantías próximas a vencer"
    :greeting="'Hola '.$recipient->name.','"
    :intro="'Se detectaron '.$assets->count().' activos cuya garantía está próxima a vencer (próximos 60 días). Revisa si requieren renovación de contrato o plan extendido.'"
    :actionUrl="$url"
    actionText="Ver listado completo"
    accent="#d97706"
    badge="Garantías"
>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin:16px 0;">
        <thead>
            <tr>
                <th style="padding:10px 16px;text-align:left;font-size:12px;color:#64748b;text-transform:uppercase;background:#f1f5f9;border-bottom:1px solid #e2e8f0;">Código</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;color:#64748b;text-transform:uppercase;background:#f1f5f9;border-bottom:1px solid #e2e8f0;">Equipo</th>
                <th style="padding:10px 16px;text-align:left;font-size:12px;color:#64748b;text-transform:uppercase;background:#f1f5f9;border-bottom:1px solid #e2e8f0;">Vence</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $a)
                <tr>
                    <td style="padding:8px 16px;font-family:monospace;font-size:12px;color:#0f172a;border-bottom:1px solid #e2e8f0;">{{ $a->internal_code }}</td>
                    <td style="padding:8px 16px;font-size:13px;color:#334155;border-bottom:1px solid #e2e8f0;">{{ $a->brand }} {{ $a->model }}</td>
                    <td style="padding:8px 16px;font-size:13px;color:#b45309;font-weight:600;border-bottom:1px solid #e2e8f0;">{{ $a->warranty_until?->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-emails.layout>
