<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiDokumenPengembangan extends Model
{
    use HasFactory;

    protected $table = 'rekomendasi_dokumen_pengembangan';

    protected $fillable = [
        'rekomendasi_fase_pengembangan_id',
        'jenis_dokumen',
        'kategori',
        'nama_file',
        'file_path',
        'file_size',
        'mime_type',
        'keterangan',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Get the fase pengembangan that owns this document.
     */
    public function fasePengembangan(): BelongsTo
    {
        return $this->belongsTo(RekomendasiFasePengembangan::class, 'rekomendasi_fase_pengembangan_id');
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
     * Get kategori display name.
     */
    public function getKategoriDisplayAttribute(): string
    {
        return match($this->kategori) {
            'dokumentasi' => 'Dokumentasi',
            'timeline' => 'Timeline',
            'tim' => 'Tim',
            'pengembangan' => 'Pengembangan',
            'instalasi' => 'Instalasi & Konfigurasi',
            'antarmuka' => 'Antarmuka',
            'sosialisasi' => 'Sosialisasi',
            'serah_terima' => 'Serah Terima',
            'testing' => 'Testing',
            default => $this->kategori,
        };
    }
}
