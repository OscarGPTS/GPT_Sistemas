@extends('layouts.app')
@section('title', 'Acceso '.$access->code)
@section('page-title', $access->code)
@section('page-subtitle', $access->name)

@section('content')
@php
    $stale = $access->isStale();
@endphp

<div class="bg-white rounded-xl shadow-card border border-slate-200 p-5 mb-5 flex flex-wrap items-start justify-between gap-3">
    <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-semibold flex-shrink-0" style="background: {{ $access->type?->color ?? '#6366f1' }}">
            🔐
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap mb-1">
                <span class="font-mono text-xs text-slate-400">{{ $access->code }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full" style="background: {{ $access->type?->color }}15; color: {{ $access->type?->color }}">{{ $access->type?->name }}</span>
                @if(! $access->is_active)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Inactivo</span>
                @endif
                @if($stale)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">⚠ Rotación pendiente</span>
                @endif
            </div>
            <h1 class="text-xl font-bold text-slate-900">{{ $access->name }}</h1>
            @if($access->description)<p class="text-sm text-slate-500 mt-1">{{ $access->description }}</p>@endif
        </div>
    </div>
    <div class="flex flex-wrap gap-2">
        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('accesses.update'))
            <a href="{{ route('accesses.edit', $access) }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-3.5 py-2 rounded-lg text-sm font-medium">Editar</a>
        @endif
        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('accesses.delete'))
            <form method="POST" action="{{ route('accesses.destroy', $access) }}" onsubmit="return confirm('¿Eliminar acceso?')">
                @csrf @method('DELETE')
                <button class="bg-red-600 hover:bg-red-500 text-white px-3.5 py-2 rounded-lg text-sm font-medium">Eliminar</button>
            </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5"
     x-data="revealController({{ $access->id }}, {{ $hasRevealWindow ? 'true' : 'false' }}, {{ $revealSecondsLeft }})">

    <div class="lg:col-span-2 space-y-5">
        <!-- Connection -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-4">Conexión</h3>
            <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div><dt class="text-xs text-slate-500 uppercase">Hostname</dt><dd class="font-mono mt-0.5">{{ $access->hostname ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">IP</dt><dd class="font-mono mt-0.5">{{ $access->ip ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">Puerto</dt><dd class="font-mono mt-0.5">{{ $access->port ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500 uppercase">URL</dt><dd class="mt-0.5">@if($access->url)<a href="{{ $access->url }}" target="_blank" class="text-brand-600 hover:underline text-xs">↗ Abrir</a>@else —@endif</dd></div>
            </dl>
        </div>

        <!-- Credentials -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-xl shadow-card text-white p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    Credenciales protegidas
                </h3>
                <div x-show="windowOpen" class="text-xs bg-emerald-500/20 text-emerald-300 px-2 py-1 rounded-full">
                    Ventana abierta · <span x-text="secondsLeft"></span>s
                </div>
            </div>

            <div class="space-y-3">
                <!-- Username -->
                <div class="bg-white/5 backdrop-blur rounded-lg p-3">
                    <div class="text-xs text-white/60 uppercase mb-1">Usuario</div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-sm" x-text="usernameVisible ? usernameValue : '{{ $access->maskedUsername() }}'"></span>
                        <div class="flex gap-1" x-show="windowOpen">
                            <button type="button" @click="reveal('username')" :disabled="!windowOpen" class="text-xs bg-white/10 hover:bg-white/20 px-2 py-1 rounded">
                                <span x-text="usernameVisible ? 'Ocultar' : 'Ver'"></span>
                            </button>
                            <button type="button" @click="copyField('username')" class="text-xs bg-white/10 hover:bg-white/20 px-2 py-1 rounded">Copiar</button>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="bg-white/5 backdrop-blur rounded-lg p-3">
                    <div class="text-xs text-white/60 uppercase mb-1">Contraseña</div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-sm" x-text="passwordVisible ? passwordValue : '••••••••••'"></span>
                        <div class="flex gap-1" x-show="windowOpen">
                            <button type="button" @click="reveal('password')" :disabled="!windowOpen" class="text-xs bg-white/10 hover:bg-white/20 px-2 py-1 rounded">
                                <span x-text="passwordVisible ? 'Ocultar' : 'Ver'"></span>
                            </button>
                            <button type="button" @click="copyField('password')" class="text-xs bg-emerald-500/30 hover:bg-emerald-500/50 px-2 py-1 rounded">📋 Copiar</button>
                        </div>
                    </div>
                </div>

                @if($access->notes)
                    <div class="bg-white/5 backdrop-blur rounded-lg p-3">
                        <div class="text-xs text-white/60 uppercase mb-1">Notas</div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm" x-text="notesVisible ? notesValue : '••• (cifradas) •••'"></span>
                            <button type="button" @click="reveal('notes')" :disabled="!windowOpen" x-show="windowOpen" class="text-xs bg-white/10 hover:bg-white/20 px-2 py-1 rounded">
                                <span x-text="notesVisible ? 'Ocultar' : 'Ver'"></span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <!-- OTP flow -->
            @if($canReveal)
                <div class="mt-5 pt-5 border-t border-white/10">
                    <div x-show="!windowOpen && !awaitingCode">
                        <p class="text-xs text-white/70 mb-3">Para revelar las credenciales necesitas un código OTP enviado a tu correo.</p>
                        <button type="button" @click="awaitingCode = true" class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg">
                            🔓 Solicitar código de revelación
                        </button>
                    </div>

                    <div x-show="awaitingCode && !windowOpen" x-cloak class="space-y-3">
                        <!-- Step 1: request OTP -->
                        <form method="POST" action="{{ route('accesses.otp.request', $access) }}" class="space-y-2" x-show="!otpSent">
                            @csrf
                            <label class="text-xs text-white/70 uppercase">Motivo (auditable) *</label>
                            <textarea name="reason" required minlength="5" rows="2" class="w-full bg-white/10 border border-white/20 rounded text-sm p-2" placeholder="Ej: necesito acceder al servidor para diagnosticar incidente TK-..."></textarea>
                            <div class="flex gap-2">
                                <button class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg">Enviar código a mi correo</button>
                                <button type="button" @click="awaitingCode = false" class="text-white/60 hover:text-white text-sm px-3">Cancelar</button>
                            </div>
                        </form>

                        @if(session('success') && str_contains(session('success'), 'Código enviado'))
                        <!-- Step 2: validate OTP -->
                        <form method="POST" action="{{ route('accesses.otp.validate', $access) }}" class="space-y-2">
                            @csrf
                            <label class="text-xs text-white/70 uppercase">Código de 6 dígitos *</label>
                            <input name="code" required pattern="[0-9]{6}" maxlength="6" inputmode="numeric"
                                   class="w-full bg-white/10 border border-white/20 rounded text-2xl font-mono p-2 text-center tracking-widest"
                                   autocomplete="one-time-code"
                                   placeholder="000000">
                            <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium px-4 py-2 rounded-lg">✓ Verificar y abrir ventana</button>
                            <p class="text-xs text-white/50 text-center">El código expira en 5 minutos</p>
                        </form>
                        @endif
                    </div>

                    <div x-show="windowOpen" x-cloak class="text-xs text-emerald-300">
                        ✓ Ventana de revelación activa · expira automáticamente en <b x-text="secondsLeft"></b> segundos.
                    </div>
                </div>
            @else
                <div class="mt-5 pt-5 border-t border-white/10 text-xs text-white/60">
                    🚫 No tienes permisos para revelar credenciales. Contacta a Seguridad.
                </div>
            @endif
        </div>

        <!-- Extra fields -->
        @if($access->extraFields->isNotEmpty())
            <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">Campos adicionales</h3>
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @foreach($access->extraFields as $f)
                            <tr>
                                <td class="px-3 py-2 text-xs text-slate-500 uppercase w-1/3">{{ $f->field_label }}</td>
                                <td class="px-3 py-2 font-mono text-sm">
                                    @if($f->is_sensitive)
                                        <span class="text-slate-400">{{ $f->maskedValue() }}</span>
                                        <span class="text-xs text-amber-600 ml-2">🔒 sensible</span>
                                    @else
                                        {{ $f->field_value }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Recent access logs -->
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-slate-900">Auditoría reciente</h3>
                @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('accesses.audit'))
                    <a href="{{ route('accesses.logs', ['access_id' => $access->id]) }}" class="text-xs text-brand-600 hover:underline">Ver todo →</a>
                @endif
            </div>
            <ul class="space-y-1.5 text-xs">
                @foreach($recentLogs as $log)
                    @php
                        $iconCls = match($log->action) {
                            'reveal' => 'bg-amber-100 text-amber-700',
                            'failed_otp' => 'bg-red-100 text-red-700',
                            'rotate' => 'bg-emerald-100 text-emerald-700',
                            'create' => 'bg-blue-100 text-blue-700',
                            default => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <li class="flex items-center gap-2 py-1.5">
                        <span class="w-2 h-2 rounded-full bg-slate-300 flex-shrink-0"></span>
                        <span class="font-mono text-slate-400 text-[11px]">{{ $log->created_at?->format('d/m H:i:s') }}</span>
                        <span class="text-xs px-2 py-0.5 rounded {{ $iconCls }}">{{ $log->actionLabel() }}</span>
                        <span class="text-slate-700">{{ $log->user?->name }}</span>
                        @if($log->reason)<span class="text-slate-500 italic">— "{{ Str::limit($log->reason, 40) }}"</span>@endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="space-y-5">
        <div class="bg-white rounded-xl shadow-card border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Información</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Ubicación</dt><dd class="text-slate-800 text-right text-xs">{{ $access->location?->fullName() ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Activo</dt><dd>@if($access->asset)<a href="{{ route('assets.show', $access->asset) }}" class="text-brand-600 font-mono text-xs">{{ $access->asset->internal_code }}</a>@else <span class="text-slate-400">—</span>@endif</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Propietario</dt><dd class="text-slate-800">{{ $access->owner?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Última rotación</dt><dd class="text-xs {{ $stale ? 'text-amber-700 font-semibold' : 'text-slate-600' }}">{{ $access->last_rotated_at?->diffForHumans() ?? 'Nunca' }}</dd></div>
                @if($access->expires_at)
                    <div class="flex justify-between"><dt class="text-slate-500">Expira</dt><dd class="text-xs {{ $access->isExpired() ? 'text-red-700 font-semibold' : 'text-slate-600' }}">{{ $access->expires_at->format('d/m/Y') }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-xs text-amber-800">
            <b>🔒 Política de seguridad</b>
            <ul class="mt-2 space-y-1 list-disc pl-4">
                <li>Las contraseñas están cifradas con AES-256</li>
                <li>Se requiere OTP válido por correo para revelarlas</li>
                <li>La ventana de revelación expira automáticamente en {{ \App\Services\AccessService::REVEAL_WINDOW_SECONDS }}s</li>
                <li>Cada acción queda registrada en auditoría inmutable</li>
            </ul>
        </div>
    </div>
</div>

<script>
function revealController(accessId, initialOpen, initialSeconds) {
    return {
        accessId,
        windowOpen: initialOpen,
        secondsLeft: initialSeconds,
        awaitingCode: false,
        otpSent: false,
        usernameVisible: false,
        passwordVisible: false,
        notesVisible: false,
        usernameValue: '',
        passwordValue: '',
        notesValue: '',
        timer: null,

        init() {
            if (this.windowOpen) this.startTimer();
        },
        startTimer() {
            this.timer = setInterval(() => {
                this.secondsLeft--;
                if (this.secondsLeft <= 0) this.expire();
            }, 1000);
        },
        expire() {
            clearInterval(this.timer);
            this.windowOpen = false;
            this.usernameVisible = false;
            this.passwordVisible = false;
            this.notesVisible = false;
            this.usernameValue = '';
            this.passwordValue = '';
            this.notesValue = '';
        },
        async reveal(field) {
            const visKey = field + 'Visible';
            const valKey = field + 'Value';
            // Toggle off if already visible
            if (this[visKey]) {
                this[visKey] = false;
                this[valKey] = '';
                return;
            }
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const resp = await fetch(`{{ url('accesses') }}/${this.accessId}/reveal`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ field }),
                });
                if (!resp.ok) {
                    const err = await resp.json();
                    alert(err.error || 'No se pudo revelar.');
                    this.expire();
                    return;
                }
                const data = await resp.json();
                this[valKey] = data.value;
                this[visKey] = true;
                this.secondsLeft = data.seconds_left;
            } catch (e) {
                alert('Error de red.');
            }
        },
        async copyField(field) {
            // If not visible, fetch first then copy without showing
            if (!this[field + 'Visible']) {
                await this.reveal(field);
                if (!this[field + 'Visible']) return;
            }
            try {
                await navigator.clipboard.writeText(this[field + 'Value']);
                alert('Copiado al portapapeles');
            } catch (e) {
                alert('No se pudo copiar');
            }
        },
    };
}
</script>
@endsection
