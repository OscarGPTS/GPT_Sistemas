<x-emails.layout
    subject="Bienvenido a GPT Services"
    :title="'¡Bienvenido, '.$user->name.'!'"
    :greeting="'Hola '.$user->name.','"
    intro="Tu cuenta en la plataforma de Inventario TI y Mesa de Servicio de GPT Services ha sido creada. Desde aquí podrás consultar tus activos, abrir tickets de soporte y gestionar tus aprobaciones."
    :actionUrl="$url"
    actionText="Iniciar sesión"
    accent="#4f46e5"
    badge="Bienvenida"
>
    <x-emails.details :rows="[
        'Nombre' => e($user->name),
        'Correo de acceso' => '<b>'.e($user->email).'</b>',
        'Área' => e($user->department ?? '—'),
        'Puesto' => e($user->position ?? '—'),
        'Código de empleado' => e($user->employee_code ?? '—'),
    ]" />

    @if($tempPassword)
        <x-emails.info-box tone="warning">
            🔐 <b>Contraseña temporal:</b>
            <code style="display:inline-block;background:#ffffff;padding:4px 10px;border-radius:4px;font-family:monospace;font-size:13px;margin-left:8px;border:1px solid #fde68a;">{{ $tempPassword }}</code>
            <br><span style="font-size:12px;">Cámbiala en tu primer inicio de sesión. También puedes ingresar con tu cuenta de Google corporativa si está habilitada.</span>
        </x-emails.info-box>
    @endif

    <p style="margin:20px 0 8px;color:#0f172a;font-size:14px;font-weight:600;">¿Qué puedes hacer?</p>
    <ul style="padding-left:20px;color:#475569;font-size:14px;line-height:1.8;">
        <li>Consultar los equipos asignados a tu cargo</li>
        <li>Abrir tickets de soporte ante cualquier incidencia</li>
        <li>Solicitar equipos, accesos o licencias mediante flujos</li>
        <li>Aprobar solicitudes del equipo a tu cargo (si aplica)</li>
    </ul>
</x-emails.layout>
