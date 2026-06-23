<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    /**
     * Validasi API key dari header X-API-Key (fallback Authorization: Bearer).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->header('X-API-Key')
            ?: $request->bearerToken();

        if (empty($plain)) {
            return response()->json([
                'success' => false,
                'message' => 'API key tidak ditemukan. Sertakan header X-API-Key.',
            ], 401);
        }

        $apiKey = ApiKey::findValid($plain);

        if (! $apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key tidak valid atau telah dinonaktifkan.',
            ], 401);
        }

        $apiKey->forceFill(['last_used_at' => now()])->saveQuietly();

        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}
