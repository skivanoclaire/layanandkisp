<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresensiKegiatan extends Model
{
    protected $fillable = [
        'slug',
        'judul',
        'deskripsi',
        'nomor_surat',
        'tanggal_mulai',
        'tanggal_selesai',
        'waktu',
        'tempat',
        'penyelenggara',
        'kontak',
        'tautan_online',
        'keterangan_online',
        'kuota_per_instansi',
        'konfirmasi_aktif',
        'presensi_aktif',
        'wajib_tanda_tangan',
        'pj_jabatan',
        'pj_nama',
        'pj_nip',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'konfirmasi_aktif' => 'boolean',
        'presensi_aktif' => 'boolean',
        'wajib_tanda_tangan' => 'boolean',
        'kuota_per_instansi' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(PresensiKegiatanPeserta::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** "Rabu, 04 November 2026" atau rentang bila lebih dari sehari. */
    public function getTanggalLabelAttribute(): string
    {
        $mulai = $this->tanggal_mulai->locale('id')->isoFormat('dddd, DD MMMM YYYY');

        if ($this->tanggal_selesai && !$this->tanggal_selesai->isSameDay($this->tanggal_mulai)) {
            return $mulai . ' s.d. ' . $this->tanggal_selesai->locale('id')->isoFormat('dddd, DD MMMM YYYY');
        }

        return $mulai;
    }

    public function urlKonfirmasi(): string
    {
        return route('kegiatan.konfirmasi', $this);
    }

    public function urlPresensi(): string
    {
        return route('kegiatan.presensi', $this);
    }
}
