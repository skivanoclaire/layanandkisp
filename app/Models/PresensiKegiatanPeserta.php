<?php

namespace App\Models;

use App\Services\FonnteWhatsappService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiKegiatanPeserta extends Model
{
    public const KONFIRMASI = [
        'hadir' => 'Hadir di lokasi',
        'online' => 'Hadir online',
    ];

    protected $fillable = [
        'presensi_kegiatan_id',
        'user_id',
        'nama',
        'nip',
        'jabatan',
        'unit_kerja_id',
        'instansi',
        'no_hp',
        'email',
        'konfirmasi',
        'konfirmasi_at',
        'catatan',
        'hadir_at',
        'tanda_tangan',
        'wa_online_terkirim_at',
        'sumber',
        'ip_address',
    ];

    protected $casts = [
        'konfirmasi_at' => 'datetime',
        'hadir_at' => 'datetime',
        'wa_online_terkirim_at' => 'datetime',
    ];

    protected $hidden = ['tanda_tangan'];

    public function getKonfirmasiLabelAttribute(): ?string
    {
        return self::KONFIRMASI[$this->konfirmasi] ?? null;
    }

    /** Kirim tautan rapat online lewat WA Fonnte Aptika; waktu kirim dicatat bila berhasil. */
    public function kirimTautanOnline(): bool
    {
        $terkirim = (new FonnteWhatsappService('aptika'))
            ->sendTautanKegiatanOnline($this->no_hp, $this->nama, $this->kegiatan);

        if ($terkirim) {
            $this->forceFill(['wa_online_terkirim_at' => now()])->save();
        }

        return $terkirim;
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(PresensiKegiatan::class, 'presensi_kegiatan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    /** Hanya angka; awalan 62 diubah menjadi 0 agar pencocokan peserta konsisten. */
    public static function normalisasiHp(?string $hp): string
    {
        $angka = preg_replace('/\D/', '', (string) $hp);

        return str_starts_with($angka, '62') ? '0' . substr($angka, 2) : $angka;
    }
}
