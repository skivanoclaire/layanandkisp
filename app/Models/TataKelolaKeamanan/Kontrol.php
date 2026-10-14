<?php

namespace App\Models\TataKelolaKeamanan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Status penerapan kontrol Annex A ISO/IEC 27001:2022 di instansi yang dievaluasi.
 */
class Kontrol extends Model
{
    protected $table = 'tata_kelola_keamanan_kontrols';

    protected $fillable = ['evaluasi_id', 'iso_kode', 'status', 'bukti', 'catatan'];

    public function evaluasi(): BelongsTo
    {
        return $this->belongsTo(Evaluasi::class, 'evaluasi_id');
    }
}
