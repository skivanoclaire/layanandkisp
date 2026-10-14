<?php

namespace App\Models\TataKelolaKeamanan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Praktik extends Model
{
    protected $table = 'tata_kelola_keamanan_praktiks';

    protected $fillable = ['evaluasi_id', 'objektif_kode', 'kode', 'nama', 'urutan'];

    public function evaluasi(): BelongsTo
    {
        return $this->belongsTo(Evaluasi::class, 'evaluasi_id');
    }

    /** Terjemahan Indonesia nama praktik (bila tersedia di referensi istilah). */
    public function getNamaIdAttribute(): ?string
    {
        return \App\Services\TataKelolaKeamanan\Istilah::praktik($this->kode);
    }

    public function aktivitas(): HasMany
    {
        return $this->hasMany(Aktivitas::class, 'praktik_id')->orderBy('level')->orderBy('urutan')->orderBy('id');
    }

    public function pemetaans(): HasMany
    {
        return $this->hasMany(Pemetaan::class, 'praktik_id');
    }
}
