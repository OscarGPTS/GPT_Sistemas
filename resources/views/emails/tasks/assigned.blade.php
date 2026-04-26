@php
    $priorityColors = ['urgent'=>'#dc2626','high'=>'#ea580c','medium'=>'#d97706','low'=>'#64748b'];
    $accent = $priorityColors[$task->priority] ?? '#4f46e5';
@endphp
<x-emails.layout
    :subject="'Tarea asignada: '.$task->title"
    title="Se te asignó una tarea"
    :greeting="'Hola '.$recipient->name.','"
    intro="Eres responsable de una nueva tarea dentro de un proyecto. Revísala para coordinar tu trabajo."
    :actionUrl="$url"
    actionText="Abrir tablero"
    :accent="$accent"
    badge="Nueva tarea"
>
    <x-emails.details :rows="[
        'Código' => '<b style=\'font-family:monospace;\'>'.$task->code.'</b>',
        'Título' => e($task->title),
        'Proyecto' => e($task->project?->name ?? '—'),
        'Prioridad' => '<span style=\'text-transform:capitalize;\'>'.e(['urgent'=>'Urgente','high'=>'Alta','medium'=>'Media','low'=>'Baja'][$task->priority] ?? $task->priority).'</span>',
        'Fecha límite' => e($task->due_at?->format('d/m/Y H:i') ?? 'Sin fecha'),
        'Columna actual' => e($task->column?->name ?? '—'),
    ]" />
    @if($task->description)
        <p style="margin:16px 0 8px;color:#0f172a;font-size:14px;font-weight:600;">Descripción:</p>
        <div style="background:#f8fafc;border-left:3px solid {{ $accent }};padding:12px 16px;color:#475569;font-size:14px;white-space:pre-wrap;border-radius:4px;">{{ $task->description }}</div>
    @endif
</x-emails.layout>
