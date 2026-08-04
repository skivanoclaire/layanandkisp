<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonsultasiAiDocument extends Model
{
    protected $table = 'konsultasi_ai_documents';

    protected $fillable = [
        'judul',
        'kategori',
        'deskripsi',
        'file_path',
        'file_name',
        'file_mime',
        'file_size',
        'konten',
        'is_active',
        'uploaded_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Apakah isi dokumen sudah bisa dipakai sebagai konteks jawaban AI.
     */
    public function hasKonten(): bool
    {
        return trim((string) $this->konten) !== '';
    }

    public function ukuranTerbaca(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes <= 0) {
            return '—';
        }

        foreach (['B', 'KB', 'MB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 1) . ' ' . $unit;
            }
            $bytes /= 1024;
        }

        return round($bytes, 1) . ' GB';
    }
}
