<?php

namespace App\Models\TataKelolaKeamanan;

use App\Services\TataKelolaKeamanan\Istilah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Satu butir pemetaan: praktik COBIT -> klausul/kontrol ISO/IEC 27001:2022 -> ketentuan regulasi.
 */
class Pemetaan extends Model
{
    public const STATUS_DRAF = 'draf';
    public const STATUS_DISEPAKATI = 'disepakati';
    public const STATUS_DIKELUARKAN = 'dikeluarkan';

    public const VERIFIKASI_DIDUKUNG = 'didukung';
    public const VERIFIKASI_RANCANGAN_PENELITI = 'rancangan_peneliti';
    public const VERIFIKASI_TIDAK_DIDUKUNG = 'tidak_didukung';

    protected $table = 'tata_kelola_keamanan_pemetaans';

    protected $fillable = [
        'evaluasi_id', 'praktik_id', 'iso_items', 'regulasi_items', 'bukti_diharapkan',
        'status', 'verifikasi', 'dokumen_acuan', 'catatan_verifikasi', 'sesi_disepakati',
        'catatan_revisi', 'alasan_dikeluarkan',
    ];

    protected $casts = [
        'iso_items' => 'array',
        'regulasi_items' => 'array',
        'dokumen_acuan' => 'array',
        'sesi_disepakati' => 'integer',
    ];

    public function evaluasi(): BelongsTo
    {
        return $this->belongsTo(Evaluasi::class, 'evaluasi_id');
    }

    public function praktik(): BelongsTo
    {
        return $this->belongsTo(Praktik::class, 'praktik_id');
    }

    public function hasilFgds(): HasMany
    {
        return $this->hasMany(HasilFgd::class, 'pemetaan_id');
    }

    /** Lolos verifikasi dokumen sehingga boleh dibahas dalam FGD. */
    public function siapFgd(): bool
    {
        return $this->status === self::STATUS_DRAF
            && in_array($this->verifikasi, [self::VERIFIKASI_DIDUKUNG, self::VERIFIKASI_RANCANGAN_PENELITI], true);
    }

    public static function labelVerifikasi(?string $verifikasi): string
    {
        return $verifikasi ? config("tata_kelola_keamanan.verifikasi.$verifikasi", $verifikasi) : 'Belum diverifikasi';
    }

    /** Kode kontrol Annex A saja (tanpa klausul). */
    public function kontrolAnnex(): array
    {
        return array_values(array_filter($this->iso_items ?? [], fn ($k) => str_starts_with($k, 'A.')));
    }

    /** Label dwibahasa: istilah resmi ISO diikuti terjemahan Indonesia. */
    public static function labelIso(string $kode): string
    {
        return Istilah::isoRingkas($kode);
    }

    /** @return array{label: string, id: ?string, penjelasan: ?string, baru: bool} */
    public static function infoIso(string $kode): array
    {
        return Istilah::iso($kode);
    }

    public static function praktikId(string $kode): ?string
    {
        return Istilah::praktik($kode);
    }
}
