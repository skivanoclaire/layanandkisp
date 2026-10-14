<?php

namespace App\Models\TataKelolaKeamanan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penilaian extends Model
{
    protected $table = 'tata_kelola_keamanan_penilaians';

    protected $fillable = ['putaran_id', 'pakar_id', 'pemetaan_id', 'setuju', 'saran'];

    protected $casts = ['setuju' => 'boolean'];

    public function putaran(): BelongsTo
    {
        return $this->belongsTo(Putaran::class, 'putaran_id');
    }

    public function pakar(): BelongsTo
    {
        return $this->belongsTo(Pakar::class, 'pakar_id');
    }

    public function pemetaan(): BelongsTo
    {
        return $this->belongsTo(Pemetaan::class, 'pemetaan_id');
    }
}
