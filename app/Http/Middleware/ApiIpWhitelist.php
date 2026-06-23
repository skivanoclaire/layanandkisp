<?php

namespace App\Http\Middleware;

use App\Models\ApiWhitelist;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiIpWhitelist
{
    /**
     * Pastikan IP pemanggil ada di daftar whitelist yang aktif.
     * request()->ip() membaca IP klien asli karena trustProxies sudah dikonfigurasi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        $allowed = ApiWhitelist::where('is_active', true)
            ->where('ip_address', $ip)
            ->exists();

        if (! $allowed) {
            return response()->json([
                'success' => false,
                'message' => 'IP tidak diizinkan mengakses API ini.',
            ], 403);
        }

        return $next($request);
    }
}
