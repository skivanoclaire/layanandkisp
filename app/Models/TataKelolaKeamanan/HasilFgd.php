<?php

namespace App\Models\TataKelolaKeamanan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Keputusan forum FGD atas satu butir pemetaan: setuju atau tidak setuju bahwa butir
 * relevan dan dapat diterapkan di DKISP, beserta saran peserta.
 */
class HasilFgd extends Model
{
    public const SETUJU = 'setuju';
    public const TIDAK_SETUJU = 'tidak_setuju';

    protected $table = 'tata_kelola_keamanan_hasil_fgds';

    protected $fillable = ['sesi_fgd_id', 'pemetaan_id', 'keputusan', 'saran'];

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiFgd::class, 'sesi_fgd_id');
    }

    public function pemetaan(): BelongsTo
    {
        return $this->belongsTo(Pemetaan::class, 'pemetaan_id');
    }
}
