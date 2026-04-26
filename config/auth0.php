<?php

return [
    'enabled' => env('AUTH0_ENABLED', false),
    'domain' => env('AUTH0_DOMAIN'),
    'client_id' => env('AUTH0_CLIENT_ID'),
    'client_secret' => env('AUTH0_CLIENT_SECRET'),
    'callback_url' => env('AUTH0_CALLBACK_URL', '/auth/auth0/callback'),
    'logout_url' => env('AUTH0_LOGOUT_URL', '/'),
    'audience' => env('AUTH0_AUDIENCE'),
    'scope' => env('AUTH0_SCOPE', 'openid profile email'),
];
