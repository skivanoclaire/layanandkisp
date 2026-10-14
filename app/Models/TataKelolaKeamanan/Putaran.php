<?php

namespace App\Models\TataKelolaKeamanan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Putaran penilaian pakar (prinsip Delphi). Butir yang dinilai pada suatu putaran adalah
 * pemetaan berstatus draf saat putaran dibuka.
 */
class Putaran extends Model
{
    public const STATUS_DIBUKA = 'dibuka';
    public const STATUS_DITUTUP = 'ditutup';

    protected $table = 'tata_kelola_keamanan_putarans';

    protected $fillable = ['evaluasi_id', 'nomor', 'status', 'dibuka_at', 'ditutup_at'];

    protected $casts = [
        'nomor' => 'integer',
        'dibuka_at' => 'datetime',
        'ditutup_at' => 'datetime',
    ];

    public function evaluasi(): BelongsTo
    {
        return $this->belongsTo(Evaluasi::class, 'evaluasi_id');
    }

    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'putaran_id');
    }
}
