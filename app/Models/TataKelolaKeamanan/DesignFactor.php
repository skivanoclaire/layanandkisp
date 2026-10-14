<?php

namespace App\Models\TataKelolaKeamanan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jawaban design factor. Kolom responden kosong ('') berarti nilai akhir hasil FGD
 * yang dimasukkan ke COBIT 2019 Design Toolkit.
 */
class DesignFactor extends Model
{
    public const NILAI_AKHIR = '';

    protected $table = 'tata_kelola_keamanan_design_factors';

    protected $fillable = ['evaluasi_id', 'responden_id', 'df_kode', 'responden', 'nilai', 'catatan'];

    protected $casts = ['nilai' => 'array'];

    public function evaluasi(): BelongsTo
    {
        return $this->belongsTo(Evaluasi::class, 'evaluasi_id');
    }
}
