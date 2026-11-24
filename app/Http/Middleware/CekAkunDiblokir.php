<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Akun yang diblokir admin langsung dikeluarkan dari sesinya, walaupun sudah login sebelumnya. */
class CekAkunDiblokir
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isBlocked()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Hubungi admin E-Layanan.');
        }

        return $next($request);
    }
}
