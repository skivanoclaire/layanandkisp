<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust hanya proxy yang didaftarkan di TRUSTED_PROXIES (koma-separated),
        // mis. IP WAF Safeline dan HAProxy. Jangan pakai '*' karena header
        // X-Forwarded-For bisa dipalsukan klien dan merusak audit log & whitelist API.
        $middleware->trustProxies(
            at: array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))))),
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->validateCsrfTokens(except: [
          'auth/sso/login',
        ]);
        // Akun yang diblokir admin dikeluarkan dari sesi aktifnya di setiap request web.
        $middleware->appendToGroup('web', \App\Http\Middleware\CekAkunDiblokir::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\SecurityHeaders::class); // audit A-21

        // Register middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'verified.user' => \App\Http\Middleware\EnsureUserIsVerified::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'api.whitelist' => \App\Http\Middleware\ApiIpWhitelist::class,
            'api.key' => \App\Http\Middleware\ApiKeyAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
