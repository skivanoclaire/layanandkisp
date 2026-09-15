<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Dokumen pendukung permohonan (Form 2.5) dan lampiran perbaikan.
 */
class DtsenRequestDocument extends Model
{
    protected $fillable = [
        'dtsen_data_request_id', 'jenis', 'nama_dokumen', 'keterangan', 'file_path', 'uploaded_by',
    ];

    public function dataRequest(): BelongsTo
    {
        return $this->belongsTo(DtsenDataRequest::class, 'dtsen_data_request_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function jenisLabels(): array
    {
        return [
            'pendukung' => 'Dokumen Pendukung',
            'perbaikan' => 'Lampiran Perbaikan',
            'lainnya' => 'Lainnya',
        ];
    }

    public function getJenisLabelAttribute(): string
    {
        return self::jenisLabels()[$this->jenis] ?? ucfirst((string) $this->jenis);
    }
}
