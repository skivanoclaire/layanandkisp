<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'tipe',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Tipe options
    public const TIPE_INDUK = 'Induk Perangkat Daerah';
    public const TIPE_CABANG = 'Cabang Perangkat Daerah';
    public const TIPE_SEKOLAH = 'Sekolah';
    public const TIPE_PUSAT = 'Instansi Pusat/Lainnya';

    public static function tipeOptions(): array
    {
        return [
            self::TIPE_INDUK,
            self::TIPE_CABANG,
            self::TIPE_SEKOLAH,
            self::TIPE_PUSAT,
        ];
    }

    // Scope untuk filter aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk filter by tipe
    public function scopeByTipe($query, string $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    // Scope untuk filter unit kerja yang ditampilkan di Layanan Digital
    // Excludes 'Instansi Pusat/Lainnya'
    public function scopeForLayananDigital($query)
    {
        return $query->whereIn('tipe', [
            self::TIPE_INDUK,
            self::TIPE_CABANG,
            self::TIPE_SEKOLAH,
        ]);
    }

    // Relationships

    /**
     * Get all users belonging to this unit kerja
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all jabatan records belonging to this unit kerja
     */
    public function jabatans()
    {
        return $this->hasMany(Jabatan::class);
    }
}
