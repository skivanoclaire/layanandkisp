<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RekomendasiSurat extends Model
{
    use HasFactory;

    protected $table = 'rekomendasi_surat';

    protected $fillable = [
        'rekomendasi_aplikasi_form_id',
        'nomor_surat_draft',
        'nomor_surat_final',
        'tanggal_surat',
        'kota',
        'referensi_hukum',
        'template_content',
        'lampiran',
        'tembusan',
        'file_draft_path',
        'file_signed_path',
        'penandatangan',
        'nip_penandatangan',
        'tanggal_ditandatangani',
    ];

    protected $casts = [
        'referensi_hukum' => 'array',
        'lampiran' => 'array',
        'tembusan' => 'array',
        'tanggal_surat' => 'date',
        'tanggal_ditandatangani' => 'date',
    ];

    /**
     * Get the rekomendasi aplikasi form that owns this surat.
     */
    public function rekomendasiAplikasiForm(): BelongsTo
    {
        return $this->belongsTo(RekomendasiAplikasiForm::class, 'rekomendasi_aplikasi_form_id');
    }

    /**
     * Get the pengiriman surat records.
     */
    public function pengiriman(): HasMany
    {
        return $this->hasMany(RekomendasiPengirimanSurat::class, 'rekomendasi_surat_id');
    }

    /**
     * Get the latest pengiriman surat.
     */
    public function latestPengiriman(): HasOne
    {
        return $this->hasOne(RekomendasiPengirimanSurat::class, 'rekomendasi_surat_id')
            ->latestOfMany();
    }

    /**
     * Get the status kementerian.
     */
    public function statusKementerian(): HasOne
    {
        return $this->hasOne(RekomendasiStatusKementerian::class, 'rekomendasi_surat_id');
    }

    /**
     * Check if surat has been signed.
     */
    public function isSigned(): bool
    {
        return !empty($this->file_signed_path);
    }

    /**
     * Check if surat has been sent.
     */
    public function isSent(): bool
    {
        return $this->pengiriman()->exists();
    }

    /**
     * Generate nomor surat otomatis.
     */
    public static function generateNomorSurat(): string
    {
        $year = date('Y');
        $month = date('m');
        $monthName = date('F');

        // Count existing letters this month
        $count = self::whereYear('tanggal_surat', $year)
            ->whereMonth('tanggal_surat', $month)
            ->count() + 1;

        $number = str_pad($count, 4, '0', STR_PAD_LEFT);

        return "{$number}/KOMINFO/REKOMENDASI-APLIKASI/{$monthName}/{$year}";
    }
}
