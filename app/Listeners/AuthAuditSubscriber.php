<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class AuthAuditSubscriber
{
    public function handleLogin(Login $event): void
    {
        AuditLog::record('login', [
            'user_id' => $event->user->id ?? null,
            'email'   => $event->user->email ?? null,
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            AuditLog::record('logout', [
                'user_id' => $event->user->id,
                'email'   => $event->user->email,
            ]);
        }
    }

    public function handleFailed(Failed $event): void
    {
        AuditLog::record('failed', [
            'user_id' => $event->user?->id,
            'email'   => $event->credentials['email'] ?? null,
        ]);
    }

    public function handleLockout(Lockout $event): void
    {
        AuditLog::record('lockout', [
            'email' => $event->request->input('email'),
        ]);
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class   => 'handleLogin',
            Logout::class  => 'handleLogout',
            Failed::class  => 'handleFailed',
            Lockout::class => 'handleLockout',
        ];
    }
}
