<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    private const AUTHORIZE_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const USERINFO_URL = 'https://www.googleapis.com/oauth2/v3/userinfo';

    public function redirect(Request $request): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        abort_if(empty($clientId), 500, 'Google OAuth no está configurado (GOOGLE_CLIENT_ID).');

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => url(config('services.google.redirect')),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'prompt' => 'select_account',
            'state' => $state,
        ]);

        return redirect(self::AUTHORIZE_URL.'?'.$params);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($error = $request->input('error')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google rechazó la autenticación: '.$error,
            ]);
        }

        $expectedState = $request->session()->pull('google_oauth_state');
        if (! $expectedState || $expectedState !== $request->input('state')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Estado de autenticación inválido. Intenta de nuevo.',
            ]);
        }

        $code = $request->input('code');
        if (empty($code)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google no devolvió un código de autorización.',
            ]);
        }

        try {
            $token = Http::asForm()->post(self::TOKEN_URL, [
                'code' => $code,
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => url(config('services.google.redirect')),
                'grant_type' => 'authorization_code',
            ])->throw()->json();

            $profile = Http::withToken($token['access_token'])
                ->get(self::USERINFO_URL)
                ->throw()
                ->json();
        } catch (\Throwable $e) {
            Log::error('Google OAuth error', ['error' => $e->getMessage()]);
            return redirect()->route('login')->withErrors([
                'email' => 'No se pudo completar el inicio de sesión con Google.',
            ]);
        }

        if (empty($profile['email'])) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google no compartió un correo electrónico.',
            ]);
        }

        $user = User::firstOrNew(['email' => $profile['email']]);
        $user->fill([
            'name' => $profile['name'] ?? $user->name ?? $profile['email'],
            'auth0_sub' => $user->auth0_sub ?? ('google|'.($profile['sub'] ?? $profile['email'])),
            'is_active' => $user->exists ? $user->is_active : true,
        ]);
        if (! $user->exists || empty($user->password)) {
            $user->password = bcrypt(Str::random(32));
        }
        $user->save();

        if (! $user->is_active) {
            return redirect()->route('login')->withErrors([
                'email' => 'Tu usuario está inactivo. Contacta al administrador.',
            ]);
        }

        if ($user->wasRecentlyCreated && $user->roles()->count() === 0) {
            $defaultRole = Role::where('name', 'user')->value('id');
            if ($defaultRole) {
                $user->roles()->sync([$defaultRole]);
            }
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
