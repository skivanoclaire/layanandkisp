<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'event',
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'meta',
    ];

    protected $casts = [
        'meta'       => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $event, array $data = []): self
    {
        return self::create(array_merge([
            'event'      => $event,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 1000),
        ], $data));
    }

    public static function eventLabels(): array
    {
        return [
            'login'            => 'Login Berhasil',
            'logout'           => 'Logout',
            'failed'           => 'Login Gagal',
            'lockout'          => 'Lockout (rate limit)',
            'password_changed' => 'Ganti Password',
        ];
    }

    public function getEventLabelAttribute(): string
    {
        return self::eventLabels()[$this->event] ?? ucfirst($this->event);
    }
}
