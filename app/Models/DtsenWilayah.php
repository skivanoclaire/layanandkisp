<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master wilayah berjenjang untuk cakupan permintaan data DTSEN.
 */
class DtsenWilayah extends Model
{
    protected $table = 'dtsen_wilayahs';

    public const TINGKAT = ['provinsi', 'kabupaten_kota', 'kecamatan', 'desa_kelurahan'];

    protected $fillable = ['parent_id', 'tingkat', 'kode', 'nama', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function tingkatLabels(): array
    {
        return [
            'provinsi' => 'Provinsi',
            'kabupaten_kota' => 'Kabupaten/Kota',
            'kecamatan' => 'Kecamatan',
            'desa_kelurahan' => 'Desa/Kelurahan',
        ];
    }

    public function getTingkatLabelAttribute(): string
    {
        return self::tingkatLabels()[$this->tingkat] ?? $this->tingkat;
    }

    /**
     * Nama lengkap berjenjang, mis. "Kalimantan Utara / Kabupaten Bulungan / Tanjung Selor".
     */
    public function getJalurAttribute(): string
    {
        $parts = [$this->nama];
        $node = $this->parent;
        $guard = 0;
        while ($node && $guard++ < 5) {
            array_unshift($parts, $node->nama);
            $node = $node->parent;
        }

        return implode(' / ', $parts);
    }

    /**
     * Ubah daftar id wilayah menjadi teks siap tampil pada ringkasan permohonan.
     *
     * @param  array<int>|null  $ids
     */
    public static function labelsFor(?array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return static::with('parent.parent')
            ->whereIn('id', $ids)
            ->get()
            ->map(fn (self $w) => $w->jalur)
            ->all();
    }
}
