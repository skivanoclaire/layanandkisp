<?php

namespace App\Models\MasterArsitektur;

use App\Models\UnitKerja;
use App\Models\User;
use App\Services\MasterArsitektur\Skema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Permohonan pembaruan Master Data Arsitektur dari pengguna instansi.
 * Baru diterapkan ke entri setelah disetujui admin.
 */
class Permohonan extends Model
{
    public const AKSI_TAMBAH = 'tambah';
    public const AKSI_UBAH = 'ubah';

    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    public const LABEL_AKSI = [
        self::AKSI_TAMBAH => 'Tambah data',
        self::AKSI_UBAH => 'Ubah data',
    ];

    public const LABEL_STATUS = [
        self::STATUS_MENUNGGU => 'Menunggu persetujuan',
        self::STATUS_DISETUJUI => 'Disetujui',
        self::STATUS_DITOLAK => 'Ditolak',
    ];

    public const WARNA_STATUS = [
        self::STATUS_MENUNGGU => 'bg-yellow-100 text-yellow-800',
        self::STATUS_DISETUJUI => 'bg-green-100 text-green-800',
        self::STATUS_DITOLAK => 'bg-red-100 text-red-800',
    ];

    protected $table = 'master_arsitektur_permohonans';

    protected $fillable = [
        'user_id', 'unit_kerja_id', 'item_id', 'aksi', 'jenis', 'data', 'data_lama',
        'catatan_pemohon', 'status', 'catatan_admin', 'diproses_oleh', 'diproses_pada',
    ];

    protected $casts = [
        'data' => 'array',
        'data_lama' => 'array',
        'diproses_pada' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function pemroses(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function menunggu(): bool
    {
        return $this->status === self::STATUS_MENUNGGU;
    }

    public function getNamaEntriAttribute(): string
    {
        return (string) ($this->data['nama'] ?? $this->data_lama['nama'] ?? '-');
    }

    public function getLabelJenisAttribute(): string
    {
        return Skema::adaJenis($this->jenis) ? Skema::label($this->jenis) : $this->jenis;
    }

    /**
     * Kolom yang diusulkan berubah: kunci => [label, lama, baru].
     * Untuk permohonan tambah: seluruh kolom yang diisi.
     */
    public function perubahan(): array
    {
        $kolom = Skema::adaJenis($this->jenis) ? Skema::kolom($this->jenis) : [];
        $lama = $this->data_lama ?? [];
        $hasil = [];

        foreach ($this->data ?? [] as $kunci => $baru) {
            $sebelum = $lama[$kunci] ?? null;

            if ($baru !== $sebelum) {
                $hasil[$kunci] = [
                    'label' => $kolom[$kunci]['label'] ?? $kunci,
                    'lama' => $sebelum,
                    'baru' => $baru,
                ];
            }
        }

        return $hasil;
    }
}
