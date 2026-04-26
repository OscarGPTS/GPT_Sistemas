@props(['tone' => 'info'])
@php
    $palette = match ($tone) {
        'success' => ['#ecfdf5', '#a7f3d0', '#065f46'],
        'warning' => ['#fffbeb', '#fde68a', '#92400e'],
        'danger'  => ['#fef2f2', '#fecaca', '#991b1b'],
        default   => ['#eff6ff', '#bfdbfe', '#1e40af'],
    };
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;">
    <tr>
        <td style="background:{{ $palette[0] }};border:1px solid {{ $palette[1] }};border-radius:8px;padding:14px 18px;color:{{ $palette[2] }};font-size:14px;">
            {{ $slot }}
        </td>
    </tr>
</table>
