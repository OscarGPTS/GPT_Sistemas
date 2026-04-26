<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Auth0Controller extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        abort_unless(config('auth0.enabled'), 404);

        $state = Str::random(40);
        $request->session()->put('auth0_state', $state);

        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => config('auth0.client_id'),
            'redirect_uri' => url(config('auth0.callback_url')),
            'scope' => config('auth0.scope'),
            'audience' => config('auth0.audience'),
            'state' => $state,
        ]);

        return redirect('https://'.config('auth0.domain').'/authorize?'.$params);
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless(config('auth0.enabled'), 404);

        abort_unless(
            $request->input('state') === $request->session()->pull('auth0_state'),
            400,
            'Invalid state'
        );

        $token = Http::asForm()->post('https://'.config('auth0.domain').'/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('auth0.client_id'),
            'client_secret' => config('auth0.client_secret'),
            'code' => $request->input('code'),
            'redirect_uri' => url(config('auth0.callback_url')),
        ])->throw()->json();

        $profile = Http::withToken($token['access_token'])
            ->get('https://'.config('auth0.domain').'/userinfo')
            ->throw()
            ->json();

        $user = User::firstOrNew(['auth0_sub' => $profile['sub']]);
        $user->fill([
            'name' => $profile['name'] ?? $profile['email'],
            'email' => $profile['email'],
            'is_active' => true,
        ]);
        if (! $user->password) {
            $user->password = Str::random(60);
        }
        $user->save();

        Auth::login($user, true);

        return redirect()->intended(route('dashboard'));
    }
}
