<?php

namespace App\Models\TataKelolaKeamanan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jawaban informan atas satu aktivitas. Menjadi bahan wawancara dan validasi bukti;
 * keputusan terpenuhi/belum tetap ditetapkan pengelola pada tabel aktivitas.
 */
class JawabanAktivitas extends Model
{
    public const JAWABAN = ['ya' => 'Ya, sudah dilakukan', 'tidak' => 'Belum dilakukan', 'tidak_tahu' => 'Tidak tahu'];

    protected $table = 'tata_kelola_keamanan_jawaban_aktivitas';

    protected $fillable = ['aktivitas_id', 'responden_id', 'jawaban', 'keterangan'];

    public function aktivitas(): BelongsTo
    {
        return $this->belongsTo(Aktivitas::class, 'aktivitas_id');
    }

    public function responden(): BelongsTo
    {
        return $this->belongsTo(Responden::class, 'responden_id');
    }

    public function lampirans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LampiranJawaban::class, 'jawaban_id')->orderBy('id');
    }
}
