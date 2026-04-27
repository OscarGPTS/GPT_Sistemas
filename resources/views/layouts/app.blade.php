<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50:  '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                    },
                    boxShadow: {
                        'soft': '0 2px 8px 0 rgb(0 0 0 / 0.04), 0 1px 2px 0 rgb(0 0 0 / 0.04)',
                        'card': '0 1px 3px 0 rgb(0 0 0 / 0.06), 0 1px 2px -1px rgb(0 0 0 / 0.04)',
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .scrollbar-thin::-webkit-scrollbar { width: 6px; height: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgb(148 163 184 / .3); border-radius: 3px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: rgb(148 163 184 / .6); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen" x-data="{ sidebarOpen: false }">
@php
    $u = auth()->user();
    $current = request()->route()?->getName() ?? '';
    $isActive = function ($names) use ($current) {
        $names = (array) $names;
        foreach ($names as $n) {
            if (str_starts_with($current, $n)) return true;
        }
        return false;
    };
    $navItem = function ($route, $label, $icon, $active) {
        $cls = $active
            ? 'bg-brand-600 text-white shadow-sm'
            : 'text-slate-300 hover:bg-slate-800 hover:text-white';
        return '<a href="'.$route.'" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition '.$cls.'">'
             . '<span class="w-5 h-5 flex-shrink-0">'.$icon.'</span>'
             . '<span>'.$label.'</span></a>';
    };

    $icon = [
        'home'       => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.5 1.5 0 012.121 0L22.28 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>',
        'assets'     => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>',
        'assign'     => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975M15 9.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'wrench'     => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/></svg>',
        'ticket'     => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/></svg>',
        'flow'       => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>',
        'check'      => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'inbox'      => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z"/></svg>',
        'chart'      => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>',
        'users'      => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>',
        'shield'     => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.249-8.25-3.285z"/></svg>',
        'bell'       => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>',
        'logout'     => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>',
        'menu'       => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>',
    ];
@endphp

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar backdrop (mobile) -->
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
         class="fixed inset-0 z-20 bg-slate-900/60 lg:hidden"></div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed lg:relative z-30 h-full w-64 bg-slate-900 text-slate-200 flex flex-col transform transition-transform duration-200 ease-in-out">
        <!-- Logo -->
        <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-800">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-bold">
                GP
            </div>
            <div>
                <div class="font-semibold text-white text-sm">GPT Services</div>
                <div class="text-xs text-slate-400">Inventario TI</div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto scrollbar-thin px-3 py-4 space-y-0.5">
            <div class="px-3 pb-2 pt-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">Principal</div>
            {!! $navItem(route('dashboard'), 'Dashboard', $icon['home'], $isActive('dashboard')) !!}
            @php $unreadInbox = $u ? $u->unreadNotifications()->count() : 0; @endphp
            <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ $isActive('notifications.') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span class="w-5 h-5 flex-shrink-0">{!! $icon['bell'] !!}</span>
                <span class="flex-1">Notificaciones</span>
                @if($unreadInbox > 0)
                    <span class="bg-rose-500 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5 min-w-[20px] text-center">{{ $unreadInbox > 99 ? '99+' : $unreadInbox }}</span>
                @endif
            </a>

            <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Inventario</div>
            {!! $navItem(route('assets.index'), 'Activos', $icon['assets'], $isActive('assets.')) !!}
            {!! $navItem(route('assignments.index'), 'Asignaciones', $icon['assign'], $isActive('assignments.')) !!}
            {!! $navItem(route('maintenance.index'), 'Mantenimiento', $icon['wrench'], $isActive('maintenance.')) !!}

            <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Servicio</div>
            {!! $navItem(route('tickets.index'), 'Tickets', $icon['ticket'], $isActive('tickets.')) !!}

            @if($u && ($u->isAdmin() || $u->hasPermission('projects.view') || $u->hasPermission('project_requests.view')))
                <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Proyectos</div>
                @if($u->isAdmin() || $u->hasPermission('projects.view'))
                    {!! $navItem(route('projects.index'), 'Tableros', '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h6v10.5h-6v-10.5zm10.5 0h6v6h-6v-6zm0 10.5h6v-2.25h-6v2.25z"/></svg>', $isActive(['projects.', 'tasks.'])) !!}
                @endif
                @if($u->isAdmin() || $u->hasPermission('project_requests.view'))
                    {!! $navItem(route('project_requests.index'), 'Solicitudes', '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>', $isActive('project_requests.')) !!}
                @endif
            @endif

            @if($u && ($u->isAdmin() || $u->hasPermission('equipment_requests.view') || $u->hasPermission('expenses.view')))
                <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Compras y Gastos</div>
                @if($u->isAdmin() || $u->hasPermission('equipment_requests.view'))
                    {!! $navItem(route('equipment_requests.index'), 'Solicitudes equipo', '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>', $isActive('equipment_requests.')) !!}
                @endif
                @if($u->isAdmin() || $u->hasPermission('expenses.view'))
                    {!! $navItem(route('expenses.index'), 'Gastos TI', '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 015.814-5.518l2.74-1.22m0 0l-5.94-2.281m5.94 2.28l-2.28 5.941"/></svg>', $isActive('expenses.')) !!}
                @endif
            @endif

            <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Flujos</div>
            @if($u && ($u->isAdmin() || $u->hasPermission('workflows.view')))
                {!! $navItem(route('workflows.index'), 'Configurar flujos', $icon['flow'], $isActive('workflows.index')) !!}
                {!! $navItem(route('workflows.instances'), 'Solicitudes', $icon['inbox'], $isActive(['workflows.instances'])) !!}
            @endif
            {!! $navItem(route('workflows.my_approvals'), 'Mis aprobaciones', $icon['check'], $isActive('workflows.my_approvals')) !!}

            @if($u && ($u->isAdmin() || $u->hasRole(['auditor','hr','it','manager'])))
                <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Análisis</div>
                {!! $navItem(route('reports.index'), 'Reportes', $icon['chart'], $isActive('reports.')) !!}
            @endif

            @if($u && $u->isAdmin())
                <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Administración</div>
                {!! $navItem(route('users.index'), 'Usuarios', $icon['users'], $isActive('users.')) !!}
                {!! $navItem(route('audit.index'), 'Auditoría', $icon['shield'], $isActive('audit.')) !!}
            @endif
        </nav>

        <!-- User card -->
        <div class="border-t border-slate-800 p-3">
            <div class="flex items-center gap-3 px-2 py-2">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-sm font-semibold">
                    {{ strtoupper(substr($u->name ?? '?', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-white truncate">{{ $u->name ?? '' }}</div>
                    <div class="text-xs text-slate-400 truncate">{{ $u->roles->first()?->label ?? 'Usuario' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" title="Cerrar sesión">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-white p-1.5 rounded hover:bg-slate-800 transition">
                        <span class="w-5 h-5 block">{!! $icon['logout'] !!}</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main content area -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Topbar -->
        <header class="bg-white border-b border-slate-200 h-16 flex items-center px-4 lg:px-6 flex-shrink-0">
            <button @click="sidebarOpen = true" class="lg:hidden p-2 text-slate-600 hover:bg-slate-100 rounded">
                <span class="w-5 h-5 block">{!! $icon['menu'] !!}</span>
            </button>
            <div class="ml-2 lg:ml-0">
                <h2 class="text-base font-semibold text-slate-800">@yield('page-title', 'Panel')</h2>
                <p class="text-xs text-slate-500">@yield('page-subtitle', 'Bienvenido de vuelta')</p>
            </div>
            <div class="ml-auto flex items-center gap-2">
                @php
                    $unreadCount = $u ? $u->unreadNotifications()->count() : 0;
                    $recentNotifications = $u ? $u->notifications()->latest()->take(7)->get() : collect();
                @endphp
                <!-- Notification bell with dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="relative p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition" title="Notificaciones">
                        <span class="w-5 h-5 block">{!! $icon['bell'] !!}</span>
                        @if($unreadCount > 0)
                            <span class="absolute top-1 right-1 min-w-[18px] h-[18px] px-1 bg-rose-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold animate-pulse">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                        @endif
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                         class="absolute right-0 mt-2 w-96 max-w-[95vw] bg-white rounded-xl shadow-2xl border border-slate-200 z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                            <div>
                                <div class="font-semibold text-slate-900 text-sm">Notificaciones</div>
                                <div class="text-xs text-slate-500">{{ $unreadCount }} sin leer</div>
                            </div>
                            @if($unreadCount > 0)
                                <form method="POST" action="{{ route('notifications.markAllRead') }}">
                                    @csrf
                                    <button class="text-xs text-brand-600 hover:text-brand-700 font-medium">Marcar todo</button>
                                </form>
                            @endif
                        </div>
                        <div class="max-h-96 overflow-y-auto scrollbar-thin">
                            @php
                                $notifIcons = [
                                    'ticket' => 'bg-rose-100 text-rose-600',
                                    'asset' => 'bg-emerald-100 text-emerald-600',
                                    'wrench' => 'bg-amber-100 text-amber-600',
                                    'check' => 'bg-brand-100 text-brand-600',
                                    'x' => 'bg-red-100 text-red-600',
                                    'shield' => 'bg-amber-100 text-amber-600',
                                    'comment' => 'bg-violet-100 text-violet-600',
                                    'info' => 'bg-slate-100 text-slate-600',
                                ];
                            @endphp
                            @forelse($recentNotifications as $n)
                                @php
                                    $iconKey = $n->data['icon'] ?? 'info';
                                    $cls = $notifIcons[$iconKey] ?? $notifIcons['info'];
                                    $unread = $n->read_at === null;
                                @endphp
                                <a href="{{ route('notifications.open', $n->id) }}"
                                   class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 border-b border-slate-100 last:border-0 transition {{ $unread ? 'bg-brand-50/40' : '' }}">
                                    <div class="w-9 h-9 rounded-lg {{ $cls }} flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm {{ $unread ? 'font-semibold text-slate-900' : 'text-slate-700' }} truncate">{{ $n->data['title'] ?? 'Notificación' }}</div>
                                        <div class="text-xs text-slate-500 truncate">{{ $n->data['message'] ?? '' }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $n->created_at->diffForHumans() }}</div>
                                    </div>
                                    @if($unread)<span class="w-2 h-2 bg-brand-500 rounded-full mt-2 flex-shrink-0"></span>@endif
                                </a>
                            @empty
                                <div class="p-8 text-center">
                                    <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                                    <div class="text-sm text-slate-500">Sin notificaciones</div>
                                </div>
                            @endforelse
                        </div>
                        <a href="{{ route('notifications.index') }}" class="block px-4 py-3 text-center text-sm text-brand-600 hover:bg-slate-50 border-t border-slate-100 font-medium">
                            Ver todas las notificaciones →
                        </a>
                    </div>
                </div>

                <!-- Approvals quick access -->
                @php
                    $pendingApprovals = app(\App\Services\WorkflowEngine::class)->pendingForUser(auth()->user())->count();
                @endphp
                @if($pendingApprovals > 0)
                <a href="{{ route('workflows.my_approvals') }}" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 rounded-lg transition text-xs font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75"/></svg>
                    {{ $pendingApprovals }} {{ Str::plural('aprobación', $pendingApprovals) }}
                </a>
                @endif

                <div class="hidden lg:block text-right pl-2 ml-1 border-l border-slate-200">
                    <div class="text-xs text-slate-500">{{ now()->locale('es')->isoFormat('dddd, D [de] MMM') }}</div>
                </div>
            </div>
        </header>

        <!-- Flash messages + main -->
        <main class="flex-1 overflow-y-auto scrollbar-thin">
            <div class="px-4 sm:px-6 lg:px-8 py-6">
                @if(session('success'))
                    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 flex items-start gap-2"
                         x-data="{ show: true }" x-show="show" x-transition>
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="flex-1 text-sm">{{ session('success') }}</div>
                        <button @click="show = false" class="text-emerald-600 hover:text-emerald-800">&times;</button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg px-4 py-3">
                        <ul class="list-disc pl-5 text-sm space-y-0.5">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</div>
</body>
</html>
