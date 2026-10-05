<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KuesionerPortalPeriode extends Model
{
    protected $table = 'kuesioner_portal_periodes';

    protected $fillable = [
        'nama',
        'keterangan',
        'is_active',
        'dibuka_at',
        'ditutup_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'dibuka_at' => 'datetime',
        'ditutup_at' => 'datetime',
    ];

    public function responses(): HasMany
    {
        return $this->hasMany(KuesionerPortalResponse::class, 'periode_id');
    }

    /**
     * Periode yang sedang menerima jawaban. Hanya satu periode yang boleh aktif.
     */
    public static function aktif(): ?self
    {
        return static::where('is_active', true)->latest('id')->first();
    }
}
