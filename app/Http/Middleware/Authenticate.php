<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     * 
     * For API-only application, always return null to prevent redirects.
     * All requests should expect JSON responses.
     */
    protected function redirectTo(Request $request): ?string
    {
        // API-only application - never redirect, always return JSON error
        return null;
    }
}
