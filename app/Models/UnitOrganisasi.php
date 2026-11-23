<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Unit organisasi di bawah Perangkat Daerah (Bidang, Sekretariat, dll).
 * Menjadi master tujuan permohonan email unit organisasi.
 */
class UnitOrganisasi extends Model
{
    public const TIPE_BIDANG = 'Bidang';
    public const TIPE_SEKRETARIAT = 'Sekretariat';
    public const TIPE_LAINNYA = 'Lainnya';

    protected $table = 'unit_organisasis';

    protected $fillable = [
        'nama',
        'tipe',
        'unit_kerja_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function tipeOptions(): array
    {
        return [self::TIPE_BIDANG, self::TIPE_SEKRETARIAT, self::TIPE_LAINNYA];
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Nama lengkap untuk ditampilkan, mis. "Bidang Statistik (Dinas Kominfo)".
     */
    public function getLabelAttribute(): string
    {
        $induk = $this->unitKerja?->nama;

        return $induk ? "{$this->nama} ({$induk})" : $this->nama;
    }
}
