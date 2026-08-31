<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class RunningText extends Model
{
    /**
     * Cache key untuk daftar teks yang sedang tayang. Dipakai layout di setiap
     * request, jadi hasilnya di-cache dan dibuang tiap kali data berubah.
     */
    public const CACHE_KEY = 'running_texts.tayang';

    protected $fillable = [
        'isi',
        'tautan',
        'urutan',
        'is_active',
        'mulai_at',
        'selesai_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'urutan'     => 'integer',
        'mulai_at'   => 'datetime',
        'selesai_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Cache dibersihkan lewat model event supaya konsisten dari mana pun
        // data diubah (controller, tinker, seeder).
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Teks disimpan sebagai plain text: tanpa tag HTML, tanpa karakter kontrol.
     * Ini lapis pertama; lapis kedua adalah escaping Blade saat render.
     */
    public function setIsiAttribute(?string $value): void
    {
        $this->attributes['isi'] = self::sanitizeIsi($value);
    }

    public static function sanitizeIsi(?string $value): string
    {
        $value = (string) $value;

        // Entity di-decode dulu supaya "&lt;script&gt;" ikut terbuang oleh strip_tags,
        // bukan tersimpan lalu ter-render sebagai tag di tempat lain.
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);

        // Buang karakter kontrol & zero-width (termasuk RTL override) yang bisa
        // dipakai menyamarkan isi teks.
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
        $value = preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}\x{FEFF}]/u', '', $value);

        // Rapikan spasi/baris baru jadi satu baris.
        $value = preg_replace('/\s+/u', ' ', $value);

        return mb_substr(trim($value), 0, 500);
    }

    /**
     * Tautan hanya boleh http/https. Skema lain (javascript:, data:, dst.)
     * dibuang supaya tidak bisa jadi vektor XSS lewat atribut href.
     */
    public function setTautanAttribute(?string $value): void
    {
        $this->attributes['tautan'] = self::sanitizeTautan($value);
    }

    public static function sanitizeTautan(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $scheme = mb_strtolower((string) parse_url($value, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_URL) ? mb_substr($value, 0, 255) : null;
    }

    /**
     * Aktif dan berada dalam rentang jadwal tayang.
     */
    public function scopeTayang(Builder $query): Builder
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('mulai_at')->orWhere('mulai_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('selesai_at')->orWhere('selesai_at', '>=', $now));
    }

    public function sedangTayang(): bool
    {
        $now = now();

        return $this->is_active
            && (! $this->mulai_at || $this->mulai_at->lte($now))
            && (! $this->selesai_at || $this->selesai_at->gte($now));
    }

    /**
     * Dipakai layout. Cache pendek (1 menit) supaya perubahan admin cepat
     * terlihat tanpa query di setiap halaman.
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function untukNavbar()
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinute(), function () {
            return self::tayang()
                ->orderBy('urutan')
                ->orderByDesc('id')
                ->get(['id', 'isi', 'tautan']);
        });
    }
}
