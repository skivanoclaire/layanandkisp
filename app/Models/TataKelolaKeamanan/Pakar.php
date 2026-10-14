<?php

namespace App\Models\TataKelolaKeamanan;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Anggota panel pakar. Pakar menilai melalui akun portal yang didaftarkan pengelola pada evaluasi.
 */
class Pakar extends Model
{
    protected $table = 'tata_kelola_keamanan_pakars';

    protected $fillable = ['evaluasi_id', 'user_id', 'instansi', 'keahlian', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function evaluasi(): BelongsTo
    {
        return $this->belongsTo(Evaluasi::class, 'evaluasi_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'pakar_id');
    }

    public function getNamaAttribute(): string
    {
        return $this->user->name ?? '-';
    }
}
