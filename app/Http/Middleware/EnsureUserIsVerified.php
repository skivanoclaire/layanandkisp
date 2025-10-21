<?php
// app/Http/Middleware/EnsureUserIsVerified.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class EnsureUserIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();

        if ($u && !$u->is_verified) {
            $msg = 'Akun anda belum diverifikasi oleh Administrator. Silahkan hubungi pengelola layanan DKISP';

            // Prioritaskan ke dashboard user jika ada
            if (Route::has('user.dashboard')) {
                return redirect()->route('user.dashboard')->with('unverified_block', $msg);
            }

            // Fallback aman: ke halaman root atau login
            if (Route::has('login')) {
                return redirect()->route('login')->withErrors(['email' => $msg]);
            }

            return redirect('/')->with('unverified_block', $msg);
        }

        return $next($request);
    }
}
