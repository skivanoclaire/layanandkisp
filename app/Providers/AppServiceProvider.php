<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View; // <-- WAJIB
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use App\Models\TteCertificateUpdateRequest;
use App\Policies\TteCertificateUpdateRequestPolicy;


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
        // Register policies
        Gate::policy(TteCertificateUpdateRequest::class, TteCertificateUpdateRequestPolicy::class);

        // Register Keycloak Socialite Provider
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('keycloak', \SocialiteProviders\Keycloak\Provider::class);
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
            View::composer(['admin.*','operator.*','profile.*','user.*'], function ($view) {
        $role = auth()->user()->role ?? 'user';
        $layout = match ($role) {
            'admin'          => 'layouts.authenticated',
            'admin-vidcon'   => 'layouts.admin-tik',
            'operator-vidcon'=> 'layouts.operator-vidcon',
            default          => 'layouts.user',
        };
        $view->with('layout', $layout);
    });
    }

}
