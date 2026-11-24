<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Header keamanan dasar (audit A-21). HSTS & CSP sengaja tidak diatur di sini (lihat catatan audit). */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
