<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiHistoriAktivitas extends Model
{
    use HasFactory;

    protected $table = 'rekomendasi_histori_aktivitas';

    public $timestamps = false; // Only has created_at

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'rekomendasi_aplikasi_form_id',
        'user_id',
        'aktivitas',
        'deskripsi',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the rekomendasi aplikasi form that owns this histori.
     */
    public function rekomendasiAplikasiForm(): BelongsTo
    {
        return $this->belongsTo(RekomendasiAplikasiForm::class, 'rekomendasi_aplikasi_form_id');
    }

    /**
     * Alias for rekomendasiAplikasiForm relationship.
     */
    public function proposal(): BelongsTo
    {
        return $this->rekomendasiAplikasiForm();
    }

    /**
     * Get the user who performed this activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new activity log entry.
     */
    public static function log(
        int $rekomendasiId,
        string $aktivitas,
        ?string $deskripsi = null,
        ?int $userId = null
    ): self {
        return self::create([
            'rekomendasi_aplikasi_form_id' => $rekomendasiId,
            'user_id' => $userId ?? auth()->id(),
            'aktivitas' => $aktivitas,
            'deskripsi' => $deskripsi,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get browser name from user agent.
     */
    public function getBrowserNameAttribute(): string
    {
        $userAgent = $this->user_agent ?? '';

        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }
}
