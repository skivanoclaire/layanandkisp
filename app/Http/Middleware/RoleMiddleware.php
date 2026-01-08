<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Terima "role:admin,admin-vidcon" ATAU "role:admin","admin-vidcon"
        $allowed = [];
        foreach ($roles as $r) {
            foreach (explode(',', $r) as $p) {
                $p = trim($p);
                if ($p !== '') $allowed[] = $p;
            }
        }

        // Support multi-role: cek apakah user punya salah satu role yang diizinkan
        $user = Auth::user();

        // Fresh load user dengan relasi roles untuk memastikan data terbaru
        $user = $user->fresh(['roles']);

        // Ambil semua role names dari relasi
        $userRoleNames = $user->roles->pluck('name')->toArray();

        // Cek apakah ada irisan antara role user dengan role yang diizinkan
        $hasAccess = !empty(array_intersect($userRoleNames, $allowed));

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
