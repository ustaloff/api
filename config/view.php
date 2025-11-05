<?php

/*
|--------------------------------------------------------------------------
| View Configuration - Minimized for API-Only Operation
|--------------------------------------------------------------------------
|
| This configuration is minimized for API-only operation. Views and Blade
| templates are not needed since the API only returns JSON responses.
| This file is kept with minimal settings to prevent Laravel errors.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths - Minimal for API-Only
    |--------------------------------------------------------------------------
    |
    | API-only applications don't need views, but Laravel requires this
    | configuration to exist. Using minimal path configuration.
    |
    */

    'paths' => [
        // Minimal path - views not used in API-only mode
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path - Minimal for API-Only
    |--------------------------------------------------------------------------
    |
    | Since views are not used, compiled view path is set to minimal value
    | to prevent Laravel configuration errors.
    |
    */

    'compiled' => storage_path('framework/views'),

];
