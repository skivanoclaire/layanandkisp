<?php

namespace App\Models\TataKelolaKeamanan;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Akun portal yang didaftarkan pengelola sebagai responden evaluasi: pimpinan yang mengisi
 * design factors dan/atau informan RACI yang mengisi kuesioner aktivitas objektif tertentu.
 */
class Responden extends Model
{
    protected $table = 'tata_kelola_keamanan_respondens';

    protected $fillable = ['evaluasi_id', 'user_id', 'jabatan', 'peran_raci', 'isi_df', 'objektif'];

    protected $casts = [
        'isi_df' => 'boolean',
        'objektif' => 'array',
    ];

    public function evaluasi(): BelongsTo
    {
        return $this->belongsTo(Evaluasi::class, 'evaluasi_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jawabans(): HasMany
    {
        return $this->hasMany(JawabanAktivitas::class, 'responden_id');
    }

    public function designFactors(): HasMany
    {
        return $this->hasMany(DesignFactor::class, 'responden_id');
    }

    /** Label yang disimpan pada kolom responden design factor. */
    public function label(): string
    {
        return mb_substr($this->jabatan.' — '.($this->user->name ?? 'akun'), 0, 100);
    }

    /** Objektif tugas yang masih termasuk objektif terpilih evaluasi. */
    public function objektifTugas(): array
    {
        $dipilih = $this->evaluasi->objektifDipilih()->pluck('kode')->all();

        return array_values(array_intersect($this->objektif ?? [], $dipilih));
    }
}
