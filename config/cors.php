<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | Configuration optimized for Bearer token authentication:
    | - supports_credentials = false (Bearer tokens don't need credentials)
    | - allowed_headers includes Authorization for Bearer tokens
    | - Dynamic origins from ALLOWED_DOMAINS environment variable
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_map(
        fn($domain) => str_starts_with($domain, 'http') ? $domain : "http://{$domain}",
        explode(',', env('ALLOWED_DOMAINS', 'localhost:3000,localhost:5173,127.0.0.1:3000,127.0.0.1:5173'))
    ),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
