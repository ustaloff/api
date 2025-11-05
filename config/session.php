<?php

/*
|--------------------------------------------------------------------------
| Session Configuration - Minimized for API-Only Operation
|--------------------------------------------------------------------------
|
| This configuration is minimized for API-only operation using Bearer tokens.
| Sessions are not needed for stateless API authentication, but this file
| is kept with minimal settings to prevent Laravel configuration errors.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver - Set to Array for API-Only
    |--------------------------------------------------------------------------
    |
    | For API-only applications using Bearer tokens, sessions are not needed.
    | Using 'array' driver ensures sessions exist only in memory during request
    | and are discarded immediately, providing stateless operation.
    |
    */

    'driver' => env('SESSION_DRIVER', 'array'),

    /*
    |--------------------------------------------------------------------------
    | Minimal Session Settings
    |--------------------------------------------------------------------------
    |
    | These are minimal required settings to prevent Laravel errors.
    | Since we're using array driver, most of these won't be used.
    |
    */

    'lifetime' => env('SESSION_LIFETIME', 1), // Minimal lifetime
    'expire_on_close' => true, // Expire immediately
    'encrypt' => false,
    'files' => storage_path('framework/sessions'),
    'connection' => null,
    'table' => 'sessions',
    'store' => null,
    'lottery' => [0, 100], // Disable session cleanup lottery
    'cookie' => 'api_session', // Simplified cookie name
    'path' => '/',
    'domain' => null,
    'secure' => env('SESSION_SECURE_COOKIE', false),
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,

];
