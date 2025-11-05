<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockCsrfCookieRoute
{
    /**
     * Handle an incoming request.
     *
     * Block access to Sanctum CSRF cookie route for pure API application.
     * This route is not needed when using Bearer token authentication.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Block access to CSRF cookie route for API-only application
        if ($request->is('sanctum/csrf-cookie')) {
            return response()->json([
                'message' => 'CSRF cookie route disabled for API-only application. Use Bearer token authentication.'
            ], 404);
        }

        return $next($request);
    }
}
