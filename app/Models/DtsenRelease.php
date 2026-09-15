<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master rilis DTSEN. Rilis baru memicu notifikasi kepada seluruh OPD pemegang
 * data agar memusnahkan salinan rilis lama (Bab VII Juknis).
 */
class DtsenRelease extends Model
{
    protected $fillable = [
        'nomor_rilis', 'tanggal_rilis', 'keterangan', 'is_active', 'notified_at', 'created_by',
    ];

    protected $casts = [
        'tanggal_rilis' => 'date',
        'is_active' => 'boolean',
        'notified_at' => 'datetime',
    ];

    public function variables(): HasMany
    {
        return $this->hasMany(DtsenVariable::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Rilis terbaru yang aktif — dipakai sebagai default katalog variabel. */
    public static function current(): ?self
    {
        return static::active()->orderByDesc('tanggal_rilis')->first();
    }
}
