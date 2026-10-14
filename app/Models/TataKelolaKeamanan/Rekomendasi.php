<?php

namespace App\Models\TataKelolaKeamanan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rekomendasi extends Model
{
    protected $table = 'tata_kelola_keamanan_rekomendasis';

    protected $fillable = ['evaluasi_id', 'objektif_kode', 'praktik_id', 'iso_kode', 'uraian', 'horizon', 'prioritas', 'penanggung_jawab'];

    protected $casts = ['prioritas' => 'integer'];

    public function evaluasi(): BelongsTo
    {
        return $this->belongsTo(Evaluasi::class, 'evaluasi_id');
    }

    public function praktik(): BelongsTo
    {
        return $this->belongsTo(Praktik::class, 'praktik_id');
    }
}
