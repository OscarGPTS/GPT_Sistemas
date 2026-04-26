@props(['rows' => []])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin:16px 0;">
    <tbody>
        @foreach($rows as $label => $value)
            <tr>
                <td style="padding:10px 16px;color:#64748b;font-size:13px;{{ !$loop->last ? 'border-bottom:1px solid #e2e8f0;' : '' }}width:40%;">{{ $label }}</td>
                <td style="padding:10px 16px;color:#0f172a;font-size:13px;font-weight:500;{{ !$loop->last ? 'border-bottom:1px solid #e2e8f0;' : '' }}">{!! $value !!}</td>
            </tr>
        @endforeach
    </tbody>
</table>
