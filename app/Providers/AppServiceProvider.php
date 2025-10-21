<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View; // <-- WAJIB


class AppServiceProvider extends ServiceProvider
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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
            View::composer(['admin.*','operator.*','profile.*','user.*'], function ($view) {
        $role = auth()->user()->role ?? 'user';
        $layout = match ($role) {
            'admin'          => 'layouts.admin',
            'admin-vidcon'   => 'layouts.admin-tik',
            'operator-vidcon'=> 'layouts.operator-vidcon',
            default          => 'layouts.user',
        };
        $view->with('layout', $layout);
    });
    }

}
