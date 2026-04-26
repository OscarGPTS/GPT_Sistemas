<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Iniciar sesión') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#eef2ff', 100: '#e0e7ff', 500: '#6366f1',
                            600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81',
                        },
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-mesh {
            background-color: #0f172a;
            background-image:
                radial-gradient(at 12% 12%, rgba(99,102,241,0.25) 0px, transparent 50%),
                radial-gradient(at 85% 20%, rgba(79,70,229,0.22) 0px, transparent 55%),
                radial-gradient(at 20% 85%, rgba(56,189,248,0.18) 0px, transparent 55%),
                radial-gradient(at 80% 90%, rgba(129,140,248,0.15) 0px, transparent 55%);
        }
    </style>
</head>
<body class="min-h-screen bg-mesh flex items-center justify-center p-4 font-sans">
    <div class="w-full max-w-5xl grid lg:grid-cols-2 bg-white rounded-2xl shadow-2xl overflow-hidden">

        <!-- Left panel: branding -->
        <div class="hidden lg:flex flex-col justify-between p-10 bg-gradient-to-br from-brand-700 via-brand-800 to-slate-900 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 30% 20%, white 1px, transparent 1px); background-size: 24px 24px;"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-lg bg-white/15 backdrop-blur flex items-center justify-center font-bold">GP</div>
                    <div>
                        <div class="font-semibold">GPT Services</div>
                        <div class="text-xs text-white/70">Inventario TI & Mesa de Servicio</div>
                    </div>
                </div>
                <h2 class="text-3xl font-bold leading-tight mb-3">
                    Gestión integral de<br>activos y servicios de TI
                </h2>
                <p class="text-white/80 text-sm leading-relaxed">
                    Control de activos, asignaciones, mantenimiento preventivo, mesa de servicio y flujos de aprobación dinámicos en una sola plataforma.
                </p>
            </div>
            <div class="relative space-y-3 text-sm">
                @foreach([
                    ['Inventario completo','Alta, baja y trazabilidad de cada equipo.'],
                    ['Mesa de servicio','Tickets, SLA y comunicación centralizada.'],
                    ['Flujos dinámicos','Aprobaciones configurables sin código.'],
                ] as $feat)
                    <div class="flex items-start gap-3 bg-white/5 backdrop-blur-sm rounded-lg p-3 border border-white/10">
                        <div class="w-8 h-8 rounded-md bg-white/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div class="font-medium">{{ $feat[0] }}</div>
                            <div class="text-white/70 text-xs">{{ $feat[1] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="relative text-xs text-white/50">© {{ date('Y') }} GPT Services. Todos los derechos reservados.</div>
        </div>

        <!-- Right panel: form -->
        <div class="p-8 sm:p-10 flex flex-col justify-center">
            <div class="max-w-sm mx-auto w-full">
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex w-12 h-12 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 items-center justify-center text-white font-bold mb-2">GP</div>
                    <div class="font-semibold text-slate-800">GPT Inventario TI</div>
                </div>
                @if($errors->any())
                    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg px-4 py-2.5 text-sm">
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
