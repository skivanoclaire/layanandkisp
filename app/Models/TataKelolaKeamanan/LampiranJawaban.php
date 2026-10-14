<?php

namespace App\Models\TataKelolaKeamanan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Berkas bukti yang diunggah informan pada jawaban aktivitas (disk privat "local").
 */
class LampiranJawaban extends Model
{
    public const MAKS_PER_AKTIVITAS = 3;
    public const MAKS_KB = 10240;
    public const TIPE = 'pdf,jpg,jpeg,png,doc,docx,xls,xlsx';

    protected $table = 'tata_kelola_keamanan_lampiran_jawabans';

    protected $fillable = ['jawaban_id', 'path', 'nama_asli', 'mime', 'ukuran'];

    protected $casts = ['ukuran' => 'integer'];

    protected static function booted(): void
    {
        static::deleting(function (LampiranJawaban $lampiran) {
            Storage::disk('local')->delete($lampiran->path);
        });
    }

    public function jawaban(): BelongsTo
    {
        return $this->belongsTo(JawabanAktivitas::class, 'jawaban_id');
    }

    /** ID evaluasi tempat lampiran berada (jawaban → responden → evaluasi). */
    public function evaluasiId(): ?int
    {
        return $this->jawaban?->responden?->evaluasi_id;
    }

    public function ukuranTerbaca(): string
    {
        return $this->ukuran >= 1048576
            ? number_format($this->ukuran / 1048576, 1, ',', '').' MB'
            : max(1, (int) round($this->ukuran / 1024)).' KB';
    }
}
