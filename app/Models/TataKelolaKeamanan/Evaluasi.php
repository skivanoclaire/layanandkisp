<?php

namespace App\Models\TataKelolaKeamanan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Satu kegiatan evaluasi (mis. Evaluasi Tata Kelola Keamanan Informasi 2026). Seluruh data tahapan
 * terikat pada evaluasi agar data simulasi terpisah dari data evaluasi nyata.
 */
class Evaluasi extends Model
{
    protected $table = 'tata_kelola_keamanan_evaluasis';

    protected $fillable = ['nama', 'instansi', 'keterangan', 'is_simulasi'];

    protected $casts = ['is_simulasi' => 'boolean'];

    protected static function booted(): void
    {
        // Cascade di database tidak memicu event model, jadi berkas bukti dihapus per folder evaluasi.
        static::deleting(function (Evaluasi $evaluasi) {
            Storage::disk('local')->deleteDirectory(self::folderBerkas($evaluasi->id));
        });
    }

    /** Folder berkas privat milik evaluasi (lampiran aktivitas dan lampiran jawaban informan). */
    public static function folderBerkas(int $id): string
    {
        return "tata-kelola-keamanan/{$id}";
    }

    public function designFactors(): HasMany
    {
        return $this->hasMany(DesignFactor::class, 'evaluasi_id');
    }

    public function respondens(): HasMany
    {
        return $this->hasMany(Responden::class, 'evaluasi_id');
    }

    public function objektifs(): HasMany
    {
        return $this->hasMany(Objektif::class, 'evaluasi_id');
    }

    public function praktiks(): HasMany
    {
        return $this->hasMany(Praktik::class, 'evaluasi_id');
    }

    public function pemetaans(): HasMany
    {
        return $this->hasMany(Pemetaan::class, 'evaluasi_id');
    }

    public function pakars(): HasMany
    {
        return $this->hasMany(Pakar::class, 'evaluasi_id');
    }

    public function putarans(): HasMany
    {
        return $this->hasMany(Putaran::class, 'evaluasi_id');
    }

    public function kontrols(): HasMany
    {
        return $this->hasMany(Kontrol::class, 'evaluasi_id');
    }

    public function rekomendasis(): HasMany
    {
        return $this->hasMany(Rekomendasi::class, 'evaluasi_id');
    }

    public function objektifDipilih()
    {
        return $this->objektifs()->where('dipilih', true)->orderBy('kode');
    }

    public function putaranAktif(): ?Putaran
    {
        return $this->putarans()->where('status', Putaran::STATUS_DIBUKA)->latest('nomor')->first();
    }
}
