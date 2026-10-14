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

    protected $table = 'tata_kelola_keamanan_pemetaans';

    protected $fillable = [
        'evaluasi_id', 'praktik_id', 'iso_items', 'regulasi_items', 'bukti_diharapkan',
        'status', 'putaran_disepakati', 'catatan_revisi',
    ];

    protected $casts = [
        'iso_items' => 'array',
        'regulasi_items' => 'array',
        'putaran_disepakati' => 'integer',
    ];

    public function evaluasi(): BelongsTo
    {
        return $this->belongsTo(Evaluasi::class, 'evaluasi_id');
    }

    public function praktik(): BelongsTo
    {
        return $this->belongsTo(Praktik::class, 'praktik_id');
    }

    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'pemetaan_id');
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
