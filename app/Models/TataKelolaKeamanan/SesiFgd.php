<?php

namespace App\Models\TataKelolaKeamanan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Sesi FGD validasi model bersama praktisi DKISP. Butir yang dibahas pada suatu sesi adalah
 * pemetaan berstatus draf yang sudah lolos verifikasi dokumen saat sesi dibuka.
 */
class SesiFgd extends Model
{
    public const STATUS_DIBUKA = 'dibuka';
    public const STATUS_DITUTUP = 'ditutup';

    protected $table = 'tata_kelola_keamanan_sesi_fgds';

    protected $fillable = ['evaluasi_id', 'nomor', 'tanggal', 'tempat', 'peserta', 'peserta_lain', 'catatan', 'status', 'ditutup_at'];

    protected $casts = [
        'nomor' => 'integer',
        'tanggal' => 'date',
        'peserta' => 'array',
        'ditutup_at' => 'datetime',
    ];

    public function evaluasi(): BelongsTo
    {
        return $this->belongsTo(Evaluasi::class, 'evaluasi_id');
    }

    public function hasils(): HasMany
    {
        return $this->hasMany(HasilFgd::class, 'sesi_fgd_id');
    }

    /** Informan (responden evaluasi) yang hadir. */
    public function respondenHadir()
    {
        return Responden::whereIn('id', $this->peserta ?? [])->where('evaluasi_id', $this->evaluasi_id)->with('user')->get();
    }

    /** Nama dan jabatan seluruh peserta, untuk ditampilkan di halaman dan laporan. */
    public function daftarPeserta(): string
    {
        return $this->respondenHadir()->toBase()
            ->map(fn (Responden $r) => ($r->user->name ?? '-').' ('.$r->jabatan.')')
            ->merge(array_filter([$this->peserta_lain]))
            ->implode('; ') ?: '-';
    }
}
