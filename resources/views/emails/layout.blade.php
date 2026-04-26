<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? 'Notificación · GPT Services' }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin:0;padding:0;background:#eef2f7;font-family:Segoe UI,-apple-system,BlinkMacSystemFont,Roboto,Arial,sans-serif;-webkit-font-smoothing:antialiased;color:#334155;line-height:1.6;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f7;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,0.06),0 1px 2px rgba(15,23,42,0.04);">

                    <!-- Header with brand gradient -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#4338ca 0%,#3730a3 50%,#1e1b4b 100%);padding:28px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:middle;">
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="width:44px;height:44px;background:rgba(255,255,255,0.15);border-radius:8px;text-align:center;vertical-align:middle;color:#ffffff;font-weight:700;font-size:16px;letter-spacing:1px;">GP</td>
                                                <td style="padding-left:14px;vertical-align:middle;">
                                                    <div style="color:#ffffff;font-weight:600;font-size:17px;">GPT Services</div>
                                                    <div style="color:rgba(255,255,255,0.7);font-size:12px;">Inventario TI & Mesa de Servicio</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    @isset($badge)
                                        <td align="right" style="vertical-align:middle;">
                                            <span style="display:inline-block;background:rgba(255,255,255,0.15);color:#ffffff;padding:6px 12px;border-radius:999px;font-size:11px;font-weight:600;letter-spacing:0.5px;text-transform:uppercase;">{{ $badge }}</span>
                                        </td>
                                    @endisset
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Accent bar -->
                    @isset($accent)
                        <tr><td style="height:4px;background:{{ $accent }};font-size:0;line-height:0;">&nbsp;</td></tr>
                    @endisset

                    <!-- Content -->
                    <tr>
                        <td style="padding:32px;">
                            @isset($greeting)
                                <p style="margin:0 0 8px;color:#64748b;font-size:14px;">{{ $greeting }}</p>
                            @endisset

                            <h1 style="margin:0 0 16px;color:#0f172a;font-size:22px;font-weight:700;line-height:1.3;">{{ $title }}</h1>

                            @if(isset($intro))
                                <p style="margin:0 0 20px;color:#475569;font-size:15px;">{{ $intro }}</p>
                            @endif

                            {{ $slot }}

                            @isset($actionUrl, $actionText)
                                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:28px 0;">
                                    <tr>
                                        <td>
                                            <a href="{{ $actionUrl }}"
                                               style="display:inline-block;padding:12px 28px;background:#4f46e5;color:#ffffff;font-weight:600;font-size:14px;text-decoration:none;border-radius:8px;">
                                                {{ $actionText }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                <p style="margin:16px 0 0;color:#94a3b8;font-size:12px;">
                                    Si el botón no funciona, copia este enlace: <br>
                                    <a href="{{ $actionUrl }}" style="color:#4f46e5;word-break:break-all;">{{ $actionUrl }}</a>
                                </p>
                            @endisset
                        </td>
                    </tr>

                    <!-- Signature -->
                    <tr>
                        <td style="padding:0 32px 24px;border-top:1px solid #e2e8f0;">
                            <p style="margin:20px 0 4px;color:#475569;font-size:14px;">Saludos,</p>
                            <p style="margin:0;color:#0f172a;font-size:14px;font-weight:600;">Equipo de TI · GPT Services</p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f8fafc;padding:20px 32px;border-top:1px solid #e2e8f0;">
                            <p style="margin:0 0 6px;color:#64748b;font-size:12px;line-height:1.5;">
                                Este correo fue enviado automáticamente por <b style="color:#334155;">GPT Services Inventario TI</b>.
                                Por favor no respondas a este mensaje.
                            </p>
                            <p style="margin:0;color:#94a3b8;font-size:11px;">
                                © {{ date('Y') }} GPT Services. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
