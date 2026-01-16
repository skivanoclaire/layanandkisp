<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiPengirimanSurat extends Model
{
    use HasFactory;

    protected $table = 'rekomendasi_pengiriman_surat';

    protected $fillable = [
        'rekomendasi_surat_id',
        'metode_pengiriman',
        'tanggal_pengiriman',
        'nomor_resi',
        'email_tujuan',
        'file_bukti_pengiriman',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pengiriman' => 'date',
    ];

    /**
     * Get the surat that owns this pengiriman.
     */
    public function surat(): BelongsTo
    {
        return $this->belongsTo(RekomendasiSurat::class, 'rekomendasi_surat_id');
    }

    /**
     * Get metode pengiriman display name.
     */
    public function getMetodePengirimanDisplayAttribute(): string
    {
        return match($this->metode_pengiriman) {
            'pos' => 'Pos Indonesia',
            'email' => 'Email Resmi',
            'online' => 'Sistem Online Kementerian',
            'kurir' => 'Kurir/Ekspedisi',
            default => $this->metode_pengiriman,
        };
    }
}
