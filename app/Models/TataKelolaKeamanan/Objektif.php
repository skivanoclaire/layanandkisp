<?php

namespace App\Models\TataKelolaKeamanan;

use App\Services\TataKelolaKeamanan\Aturan;
use App\Services\TataKelolaKeamanan\Istilah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Objektif extends Model
{
    protected $table = 'tata_kelola_keamanan_objektifs';

    protected $fillable = ['evaluasi_id', 'kode', 'skor_kepentingan', 'terkait_keamanan', 'dipilih', 'target_manual', 'alasan'];

    protected $casts = [
        'skor_kepentingan' => 'integer',
        'terkait_keamanan' => 'boolean',
        'dipilih' => 'boolean',
        'target_manual' => 'integer',
    ];

    public function evaluasi(): BelongsTo
    {
        return $this->belongsTo(Evaluasi::class, 'evaluasi_id');
    }

    public function getNamaAttribute(): string
    {
        return config("tata_kelola_keamanan.objektif.{$this->kode}.nama", $this->kode);
    }

    public function getNamaIdAttribute(): ?string
    {
        return Istilah::objektif($this->kode)['nama_id'];
    }

    public function getPenjelasanAttribute(): ?string
    {
        return Istilah::objektif($this->kode)['penjelasan'];
    }

    public function getDomainAttribute(): string
    {
        return substr($this->kode, 0, 3);
    }

    /** Target dari skor kepentingan, kecuali pengelola menetapkan target manual. */
    public function getTargetLevelAttribute(): ?int
    {
        if ($this->target_manual) {
            return $this->target_manual;
        }

        return $this->skor_kepentingan === null ? null : Aturan::targetLevel($this->skor_kepentingan);
    }
}
