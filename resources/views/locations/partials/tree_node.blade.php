@php
    $hasChildren = $node->children->isNotEmpty();
@endphp
<li>
    <div class="flex items-center gap-2 py-2 px-2 rounded-lg hover:bg-slate-50 group" style="padding-left: {{ ($depth * 24) + 8 }}px;">
        <div class="flex items-center gap-2 flex-1 min-w-0">
            @if($hasChildren)
                <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
            @else
                <span class="w-3 inline-block flex-shrink-0"></span>
            @endif
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background: {{ $node->typeColor() }}"></span>
            <span class="font-medium text-slate-800 truncate">{{ $node->name }}</span>
            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $node->typeLabel() }}</span>
            @if(!$node->is_active)
                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-200 text-slate-500">Inactiva</span>
            @endif
            @if($node->deviceUsers->isNotEmpty() ?? false)
                <span class="text-xs text-slate-400">·</span>
                <span class="text-xs text-slate-500">{{ $node->deviceUsers->count() }} usuarios</span>
            @endif
        </div>
        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('locations.manage'))
        <div class="opacity-0 group-hover:opacity-100 flex items-center gap-1 flex-shrink-0">
            <a href="{{ route('locations.create') }}?parent_id={{ $node->id }}" class="text-xs text-brand-600 hover:underline" title="Agregar sub-ubicación">+ Hijo</a>
            <a href="{{ route('locations.edit', $node) }}" class="text-xs text-slate-500 hover:underline ml-2">Editar</a>
            <form method="POST" action="{{ route('locations.destroy', $node) }}" class="inline ml-2" onsubmit="return confirm('¿Eliminar?')">
                @csrf @method('DELETE')
                <button class="text-xs text-red-500 hover:underline">Eliminar</button>
            </form>
        </div>
        @endif
    </div>
    @if($hasChildren)
        <ul class="space-y-1">
            @foreach($node->children as $child)
                @include('locations.partials.tree_node', ['node' => $child, 'depth' => $depth + 1])
            @endforeach
        </ul>
    @endif
</li>
