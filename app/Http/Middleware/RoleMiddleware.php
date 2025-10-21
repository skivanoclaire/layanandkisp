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

        if (!in_array(Auth::user()->role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
