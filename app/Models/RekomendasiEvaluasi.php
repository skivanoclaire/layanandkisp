<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiEvaluasi extends Model
{
    use HasFactory;

    protected $table = 'rekomendasi_evaluasi';

    protected $fillable = [
        'rekomendasi_aplikasi_form_id',
        'periode',
        'tanggal_evaluasi',
        'kebijakan_internal',
        'rating_fungsionalitas',
        'rating_keamanan',
        'rating_performance',
        'rating_ux',
        'jumlah_pengguna',
        'frekuensi_akses',
        'fitur_populer',
        'file_survey',
        'feedback_pengguna',
        'file_laporan_evaluasi',
        'rekomendasi_tindak_lanjut',
        'file_laporan_pimpinan',
        'tanggal_penyampaian_pimpinan',
    ];

    protected $casts = [
        'tanggal_evaluasi' => 'date',
        'tanggal_penyampaian_pimpinan' => 'date',
        'rating_fungsionalitas' => 'integer',
        'rating_keamanan' => 'integer',
        'rating_performance' => 'integer',
        'rating_ux' => 'integer',
        'jumlah_pengguna' => 'integer',
    ];

    /**
     * Get the rekomendasi aplikasi form that owns this evaluasi.
     */
    public function rekomendasiAplikasiForm(): BelongsTo
    {
        return $this->belongsTo(RekomendasiAplikasiForm::class, 'rekomendasi_aplikasi_form_id');
    }

    /**
     * Get average rating across all categories.
     */
    public function getAverageRatingAttribute(): float
    {
        $ratings = [
            $this->rating_fungsionalitas,
            $this->rating_keamanan,
            $this->rating_performance,
            $this->rating_ux,
        ];

        $validRatings = array_filter($ratings, fn($r) => $r !== null);

        if (empty($validRatings)) {
            return 0;
        }

        return round(array_sum($validRatings) / count($validRatings), 2);
    }

    /**
     * Get rating color based on value.
     */
    public function getRatingColorAttribute(): string
    {
        $avg = $this->average_rating;

        if ($avg >= 4) {
            return 'success';
        } elseif ($avg >= 3) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    /**
     * Get rekomendasi tindak lanjut display name.
     */
    public function getRekomendasiTindakLanjutDisplayAttribute(): string
    {
        return match($this->rekomendasi_tindak_lanjut) {
            'tetap_digunakan' => 'Tetap Digunakan',
            'perlu_pengembangan' => 'Perlu Pengembangan',
            'perlu_perbaikan' => 'Perlu Perbaikan',
            'penghentian' => 'Penghentian Aplikasi',
            default => $this->rekomendasi_tindak_lanjut,
        };
    }

    /**
     * Get rekomendasi tindak lanjut badge color.
     */
    public function getRekomendasiBadgeColorAttribute(): string
    {
        return match($this->rekomendasi_tindak_lanjut) {
            'tetap_digunakan' => 'success',
            'perlu_pengembangan' => 'info',
            'perlu_perbaikan' => 'warning',
            'penghentian' => 'danger',
            default => 'secondary',
        };
    }
}
