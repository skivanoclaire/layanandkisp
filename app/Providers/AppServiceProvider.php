<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View; // <-- WAJIB
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use App\Models\TteCertificateUpdateRequest;
use App\Policies\TteCertificateUpdateRequestPolicy;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind anhskohbo/no-captcha under a dedicated key because mews/captcha
        // also binds the default 'captcha' key and overwrites it at boot.
        $this->app->singleton('nocaptcha', function () {
            return new \Anhskohbo\NoCaptcha\NoCaptcha(
                env('NOCAPTCHA_SECRET'),
                env('NOCAPTCHA_SITEKEY'),
                ['timeout' => 30]
            );
        });
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

        // Register Audit Log subscriber for auth events
        Event::subscribe(\App\Listeners\AuthAuditSubscriber::class);

        // Dedicated Google reCAPTCHA validation rule (mews/captcha occupies the
        // 'captcha' rule for the login image captcha).
        Validator::extend('recaptcha', function ($attribute, $value) {
            return app('nocaptcha')->verifyResponse($value, request()->getClientIp());
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

        View::composer('layouts.authenticated', function ($view) {
            $user = auth()->user();
            $counts = [];
            if ($user && $user->hasAnyPermission([
                'admin.permohonan', 'admin.email', 'admin.subdomain', 'admin.rekomendasi',
                'Kelola Bantuan TTE', 'Kelola Registrasi TTE', 'Kelola Reset Passphrase TTE',
                'Kelola Permohonan PSE',
            ])) {
                $counts = app(\App\Services\AdminPendingCountsService::class)->counts();
            }
            $view->with('pendingCounts', $counts);
        });
    }

}
