<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiDokumenUsulan extends Model
{
    use HasFactory;

    protected $table = 'rekomendasi_dokumen_usulan';

    protected $fillable = [
        'rekomendasi_aplikasi_form_id',
        'jenis_dokumen',
        'nama_file',
        'file_path',
        'file_size',
        'mime_type',
        'versi',
        'keterangan',
        'uploaded_by',
    ];

    protected $casts = [
        'versi' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * Get the rekomendasi aplikasi form that owns this document.
     */
    public function rekomendasiAplikasiForm(): BelongsTo
    {
        return $this->belongsTo(RekomendasiAplikasiForm::class, 'rekomendasi_aplikasi_form_id');
    }

    /**
     * Get the user who uploaded this document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get human-readable file size.
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get display name for jenis_dokumen.
     */
    public function getJenisDokumenDisplayAttribute(): string
    {
        return match($this->jenis_dokumen) {
            'analisis_kebutuhan' => 'Dokumen Analisis Kebutuhan',
            'perencanaan' => 'Dokumen Perencanaan',
            'manajemen_risiko' => 'Dokumen Manajemen Risiko',
            default => $this->jenis_dokumen,
        };
    }
}
