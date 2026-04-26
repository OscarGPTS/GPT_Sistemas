<x-emails.layout
    :subject="'Nuevo comentario en ticket '.$ticket->code"
    title="Nuevo comentario en tu ticket"
    :greeting="'Hola '.$recipient->name.','"
    intro="Se ha añadido un nuevo comentario a un ticket en el que participas."
    :actionUrl="$url"
    actionText="Ver conversación"
    accent="#4f46e5"
    badge="Comentario"
>
    <x-emails.details :rows="[
        'Código' => '<b style=\'font-family:monospace;\'>'.$ticket->code.'</b>',
        'Asunto' => e($ticket->subject),
        'Comentado por' => e($comment->user?->name ?? '—'),
        'Fecha' => e($comment->created_at?->format('d/m/Y H:i')),
    ]" />

    <p style="margin:16px 0 8px;color:#0f172a;font-size:14px;font-weight:600;">Comentario:</p>
    <div style="background:#f8fafc;border-left:3px solid #4f46e5;padding:12px 16px;color:#475569;font-size:14px;white-space:pre-wrap;border-radius:4px;">{{ $comment->body }}</div>
</x-emails.layout>
