<?php

namespace App\Models\TataKelolaKeamanan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aktivitas extends Model
{
    protected $table = 'tata_kelola_keamanan_aktivitas';

    protected $fillable = [
        'praktik_id', 'level', 'uraian', 'bukti_diharapkan', 'terpenuhi', 'bukti_ditemukan',
        'sumber_bukti', 'informan', 'lampiran_path', 'dinilai_at', 'urutan',
    ];

    protected $casts = [
        'level' => 'integer',
        'terpenuhi' => 'boolean',
        'sumber_bukti' => 'array',
        'dinilai_at' => 'datetime',
    ];

    public function praktik(): BelongsTo
    {
        return $this->belongsTo(Praktik::class, 'praktik_id');
    }

    public function jawabans(): HasMany
    {
        return $this->hasMany(JawabanAktivitas::class, 'aktivitas_id');
    }
}
