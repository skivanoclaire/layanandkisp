<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Fitur 4.3 - Token/tautan unduh data DTSEN, masa aktif 30 hari kalender.
 */
class DtsenAccessToken extends Model
{
    /** Peringatan kedaluwarsa dikirim sekian hari sebelum `expires_at`. */
    public const WARN_BEFORE_DAYS = 7;

    protected $fillable = [
        'dtsen_data_request_id', 'token', 'metode', 'file_path', 'nama_berkas', 'parameter',
        'catatan', 'issued_by', 'issued_at', 'expires_at', 'revoked_at', 'expiry_warned_at',
        'download_count', 'max_download',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'expiry_warned_at' => 'datetime',
        'download_count' => 'integer',
        'max_download' => 'integer',
    ];

    protected $hidden = ['token'];

    public function dataRequest(): BelongsTo
    {
        return $this->belongsTo(DtsenDataRequest::class, 'dtsen_data_request_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function downloadLogs(): HasMany
    {
        return $this->hasMany(DtsenDownloadLog::class)->orderByDesc('downloaded_at');
    }

    public static function generateToken(): string
    {
        do {
            $token = Str::lower(Str::random(48));
        } while (static::where('token', $token)->exists());

        return $token;
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function reachedDownloadLimit(): bool
    {
        return $this->max_download !== null && $this->download_count >= $this->max_download;
    }

    public function isUsable(): bool
    {
        return ! $this->isRevoked() && ! $this->isExpired() && ! $this->reachedDownloadLimit();
    }

    /** Sisa hari sebelum token kedaluwarsa (negatif bila sudah lewat). */
    public function daysLeft(): ?int
    {
        if (! $this->expires_at) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->expires_at->copy()->startOfDay(), false);
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->isRevoked() => 'Dicabut',
            $this->isExpired() => 'Kedaluwarsa',
            $this->reachedDownloadLimit() => 'Batas Unduh Tercapai',
            default => 'Aktif',
        };
    }

    public function statusBadgeClass(): string
    {
        return match (true) {
            $this->isRevoked() => 'bg-red-100 text-red-800',
            $this->isExpired() => 'bg-gray-200 text-gray-600',
            $this->reachedDownloadLimit() => 'bg-orange-100 text-orange-800',
            default => 'bg-green-100 text-green-800',
        };
    }

    /** Catat satu unduhan beserta jejak auditnya. */
    public function recordDownload(?User $user): DtsenDownloadLog
    {
        $this->increment('download_count');

        return DtsenDownloadLog::create([
            'dtsen_access_token_id' => $this->id,
            'dtsen_data_request_id' => $this->dtsen_data_request_id,
            'user_id' => $user?->id,
            'ip_address' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 500),
            'downloaded_at' => now(),
        ]);
    }
}
