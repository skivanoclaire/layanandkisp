<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * 1️⃣  Daftarkan alias middleware “role”
         *     Sekarang Route::middleware('role:admin') dll akan dikenali.
         */
        Route::aliasMiddleware('role', \App\Http\Middleware\RoleMiddleware::class);

        /**
         * 2️⃣  (Opsional) Kalau ingin membuat group custom:
         * Route::middlewareGroup('admin', ['role:admin', 'auth']);
         */

        // panggil parent boot agar file‐file route dimuat
        parent::boot();
    }
}
