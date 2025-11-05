<?php

/*
|--------------------------------------------------------------------------
| Broadcasting Configuration - Minimized for API-Only Operation
|--------------------------------------------------------------------------
|
| This configuration is minimized for API-only operation. Broadcasting
| is typically not needed for simple APIs unless real-time features
| are specifically required. Default is set to 'null' to disable.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster - Disabled for API-Only
    |--------------------------------------------------------------------------
    |
    | Set to 'null' by default for API-only applications. Can be changed
    | to 'redis', 'pusher', or other drivers if real-time features are needed.
    |
    */

    'default' => env('BROADCAST_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections - Minimal Configuration
    |--------------------------------------------------------------------------
    |
    | Keeping minimal connections for potential future use. Most API-only
    | applications won't need broadcasting unless implementing real-time features.
    |
    */

    'connections' => [

        'null' => [
            'driver' => 'null',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        // Uncomment and configure if real-time broadcasting is needed
        /*
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'host' => env('PUSHER_HOST') ?: 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com',
                'port' => env('PUSHER_PORT', 443),
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'encrypted' => true,
                'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
            ],
        ],
        */

    ],

];
