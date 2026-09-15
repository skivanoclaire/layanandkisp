<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Form 3.3 - Berita Acara Klarifikasi antara Tim Pelaksana dan perwakilan OPD.
 */
class DtsenClarification extends Model
{
    protected $fillable = [
        'dtsen_data_request_id', 'tanggal', 'tempat_media', 'peserta',
        'pokok_klarifikasi', 'hasil', 'file_path', 'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function dataRequest(): BelongsTo
    {
        return $this->belongsTo(DtsenDataRequest::class, 'dtsen_data_request_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
