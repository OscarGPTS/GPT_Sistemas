@extends('layouts.app')
@section('title', $access->exists ? 'Editar acceso' : 'Nuevo acceso')
@section('page-title', $access->exists ? 'Editar acceso' : 'Nuevo acceso')
@section('page-subtitle', $access->exists ? $access->code : 'Registra credenciales en el vault cifrado')

@section('content')
<form method="POST" action="{{ $access->exists ? route('accesses.update', $access) : route('accesses.store') }}"
      class="bg-white rounded-xl shadow-card border border-slate-200 p-6 space-y-6 max-w-5xl"
      x-data="{
        extras: @js(old('extras', $access->extraFields->map(fn($e) => [
            'field_name' => $e->field_name,
            'field_label' => $e->field_label,
            'field_value' => $e->field_value,
            'is_sensitive' => $e->is_sensitive,
        ])->values()->all())),
        showPassword: false,
        async generatePass() {
            const r = await fetch('{{ route('accesses.generate_password') }}', { headers: { Accept: 'application/json' } });
            const d = await r.json();
            this.$refs.password.value = d.password;
            this.showPassword = true;
        }
      }">
    @csrf
    @if($access->exists) @method('PUT') @endif

    @php
        $input = 'mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
        $label = 'block text-xs font-semibold text-slate-700 uppercase tracking-wide';
    @endphp

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Identificación</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="{{ $label }}">Tipo de acceso *</label>
                <select name="type_id" required class="{{ $input }}">
                    <option value="">— Selecciona —</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" @selected(old('type_id', $access->type_id) == $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Nombre *</label>
                <input name="name" value="{{ old('name', $access->name) }}" required class="{{ $input }}" placeholder="Ej. Servidor producción Web1">
            </div>
            <div class="md:col-span-2">
                <label class="{{ $label }}">Descripción</label>
                <input name="description" value="{{ old('description', $access->description) }}" class="{{ $input }}">
            </div>
        </div>
    </div>

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Conexión</h3>
        <div class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="{{ $label }}">Hostname</label>
                <input name="hostname" value="{{ old('hostname', $access->hostname) }}" class="{{ $input }}" placeholder="srv-prod-01">
            </div>
            <div>
                <label class="{{ $label }}">IP</label>
                <input name="ip" value="{{ old('ip', $access->ip) }}" class="{{ $input }}" placeholder="192.168.1.10">
            </div>
            <div>
                <label class="{{ $label }}">Puerto</label>
                <input type="number" name="port" value="{{ old('port', $access->port) }}" min="1" max="65535" class="{{ $input }}" placeholder="22">
            </div>
            <div>
                <label class="{{ $label }}">URL</label>
                <input type="url" name="url" value="{{ old('url', $access->url) }}" class="{{ $input }}" placeholder="https://...">
            </div>
        </div>
    </div>

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Credenciales <span class="text-xs font-normal text-slate-500 ml-2">(cifradas AES-256)</span></h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="{{ $label }}">Usuario</label>
                <input name="username" value="{{ old('username', $access->username) }}" class="{{ $input }}" autocomplete="off">
            </div>
            <div>
                <label class="{{ $label }}">Contraseña {{ $access->exists ? '(dejar vacío para no cambiar)' : '*' }}</label>
                <div class="relative">
                    <input x-ref="password" :type="showPassword ? 'text' : 'password'"
                           name="password"
                           value="{{ old('password') }}"
                           {{ $access->exists ? '' : 'required' }}
                           class="{{ $input }} pr-20 font-mono"
                           autocomplete="new-password">
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 flex gap-1">
                        <button type="button" @click="showPassword = !showPassword" class="text-xs text-slate-500 hover:text-slate-700 px-2 py-1">
                            <span x-show="!showPassword">👁</span>
                            <span x-show="showPassword">🙈</span>
                        </button>
                        <button type="button" @click="generatePass" class="text-xs text-brand-600 hover:text-brand-700 px-2 py-1" title="Generar contraseña aleatoria">🎲</button>
                    </div>
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="{{ $label }}">Notas (cifradas)</label>
                <textarea name="notes" rows="3" class="{{ $input }}">{{ old('notes', $access->notes) }}</textarea>
            </div>
        </div>
    </div>

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Vinculación</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="{{ $label }}">Ubicación</label>
                <select name="location_id" class="{{ $input }}">
                    <option value="">—</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected(old('location_id', $access->location_id) == $loc->id)>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Activo</label>
                <select name="asset_id" class="{{ $input }}">
                    <option value="">—</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" @selected(old('asset_id', $access->asset_id) == $asset->id)>{{ $asset->internal_code }} · {{ $asset->brand }} {{ $asset->model }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Propietario</label>
                <select name="owner_id" class="{{ $input }}">
                    <option value="">—</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('owner_id', $access->owner_id) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div>
        <div class="flex justify-between items-center pb-2 border-b border-slate-100 mb-3">
            <h3 class="font-semibold text-slate-900">Campos adicionales</h3>
            <button type="button"
                    @click="extras.push({field_name:'', field_label:'', field_value:'', is_sensitive:false})"
                    class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white text-sm px-3 py-1.5 rounded-lg">
                + Agregar campo
            </button>
        </div>
        <div class="space-y-2">
            <template x-for="(extra, i) in extras" :key="i">
                <div class="grid grid-cols-12 gap-2 items-center bg-slate-50 rounded-lg p-3 border border-slate-200">
                    <input :name="`extras[${i}][field_name]`" x-model="extra.field_name" placeholder="campo (ej. ssid)" class="col-span-3 border border-slate-300 rounded px-2 py-1 text-sm">
                    <input :name="`extras[${i}][field_label]`" x-model="extra.field_label" placeholder="Etiqueta" class="col-span-3 border border-slate-300 rounded px-2 py-1 text-sm">
                    <input :name="`extras[${i}][field_value]`" x-model="extra.field_value" placeholder="Valor" class="col-span-4 border border-slate-300 rounded px-2 py-1 text-sm font-mono">
                    <label class="col-span-1 text-xs flex items-center gap-1">
                        <input type="checkbox" :name="`extras[${i}][is_sensitive]`" value="1" x-model="extra.is_sensitive" class="rounded text-brand-600">
                        🔒
                    </label>
                    <button type="button" @click="extras.splice(i,1)" class="col-span-1 text-red-500 hover:text-red-700 text-xs">×</button>
                </div>
            </template>
        </div>
    </div>

    <div>
        <h3 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Rotación y expiración</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="{{ $label }}">Expira el</label>
                <input type="date" name="expires_at" value="{{ old('expires_at', optional($access->expires_at)->format('Y-m-d')) }}" class="{{ $input }}">
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center text-sm">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $access->is_active ?? true)) class="mr-2 rounded text-brand-600">
                    Acceso activo
                </label>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <a href="{{ route('accesses.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-800 text-sm">Cancelar</a>
        <button class="bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium shadow-sm">
            {{ $access->exists ? 'Actualizar' : 'Guardar acceso' }}
        </button>
    </div>
</form>
@endsection
