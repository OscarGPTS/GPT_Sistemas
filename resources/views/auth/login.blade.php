@extends('layouts.guest')
@section('title', 'Iniciar sesión')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Bienvenido de vuelta</h1>
    <p class="text-sm text-slate-500 mt-1">Inicia sesión para acceder al panel</p>
</div>

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo electrónico</label>
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
            </span>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   placeholder="tu@correo.com"
                   class="w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition">
        </div>
    </div>
    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label class="block text-sm font-medium text-slate-700">Contraseña</label>
        </div>
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
            </span>
            <input type="password" name="password" required placeholder="••••••••"
                   class="w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition">
        </div>
    </div>
    <label class="flex items-center text-sm text-slate-600">
        <input type="checkbox" name="remember" class="mr-2 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
        Mantener sesión iniciada
    </label>
    <button type="submit"
            class="w-full bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white py-2.5 rounded-lg font-medium shadow-sm transition text-sm">
        Iniciar sesión
    </button>
</form>

@if($googleEnabled || $auth0Enabled)
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
        <div class="relative flex justify-center text-xs">
            <span class="bg-white px-3 text-slate-400 uppercase tracking-wider font-medium">O continúa con</span>
        </div>
    </div>
@endif

@if($googleEnabled)
    <a href="{{ route('login.google') }}"
       class="flex items-center justify-center gap-3 w-full border border-slate-300 bg-white text-slate-700 py-2.5 rounded-lg hover:bg-slate-50 hover:border-slate-400 transition font-medium text-sm">
        <svg class="w-5 h-5" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
            <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
            <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
            <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
        </svg>
        Iniciar sesión con Google
    </a>
@endif
@if($auth0Enabled)
    <a href="{{ route('auth0.redirect') }}"
       class="mt-2 flex items-center justify-center gap-2 w-full border border-slate-800 bg-slate-900 text-white py-2.5 rounded-lg hover:bg-slate-800 transition text-sm font-medium">
        Iniciar sesión con Auth0 (SSO)
    </a>
@endif

<div class="mt-8 p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-500">
    <div class="font-semibold text-slate-600 mb-1">🔑 Credenciales de demo</div>
    <code class="block">admin@gptservices.com / password</code>
    <code class="block">ti@gptservices.com / password</code>
    <code class="block">gerente@gptservices.com / password</code>
</div>
@endsection
