<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FasePengembanganDokumen extends Model
{
    use HasFactory;

    protected $table = 'fase_pengembangan_dokumen';

    const FASE_RANCANG_BANGUN = 'rancang_bangun';
    const FASE_IMPLEMENTASI = 'implementasi';
    const FASE_UJI_KELAIKAN = 'uji_kelaikan';
    const FASE_PEMELIHARAAN = 'pemeliharaan';
    const FASE_EVALUASI = 'evaluasi';

    const FASE_LABELS = [
        self::FASE_RANCANG_BANGUN => 'Rancang Bangun',
        self::FASE_IMPLEMENTASI => 'Implementasi',
        self::FASE_UJI_KELAIKAN => 'Uji Kelaikan',
        self::FASE_PEMELIHARAAN => 'Pemeliharaan',
        self::FASE_EVALUASI => 'Evaluasi',
    ];

    protected $fillable = [
        'rekomendasi_aplikasi_form_id',
        'fase',
        'nama_file',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by',
    ];

    /**
     * Get the proposal this document belongs to.
     */
    public function proposal()
    {
        return $this->belongsTo(RekomendasiAplikasiForm::class, 'rekomendasi_aplikasi_form_id');
    }

    /**
     * Get the user who uploaded this document.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get human-readable file size.
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }

    /**
     * Get fase display name.
     */
    public function getFaseDisplayAttribute(): string
    {
        return self::FASE_LABELS[$this->fase] ?? $this->fase;
    }
}
