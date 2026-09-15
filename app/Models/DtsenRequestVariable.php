<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Variabel yang dipilih pada satu permohonan beserta kegunaan/alasan kebutuhannya
 * (Form 2.3). Verifikator substansi dapat menolak sebagian variabel.
 */
class DtsenRequestVariable extends Model
{
    protected $fillable = [
        'dtsen_data_request_id', 'dtsen_variable_id', 'kegunaan',
        'disetujui', 'catatan_verifikator',
    ];

    protected $casts = [
        'disetujui' => 'boolean',
    ];

    public function dataRequest(): BelongsTo
    {
        return $this->belongsTo(DtsenDataRequest::class, 'dtsen_data_request_id');
    }

    public function variable(): BelongsTo
    {
        return $this->belongsTo(DtsenVariable::class, 'dtsen_variable_id');
    }
}
