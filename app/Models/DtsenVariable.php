<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master variabel/indikator DTSEN (dikelola Bapperida selaku koordinator Forum SDD).
 */
class DtsenVariable extends Model
{
    protected $fillable = [
        'kode', 'nama', 'deskripsi', 'nilai_kode', 'kategori', 'set_data', 'sensitivitas', 'bisa_filter',
        'satuan', 'level_minimal', 'dtsen_release_id', 'is_active', 'urutan',
    ];

    protected $casts = [
        'level_minimal' => 'integer',
        'bisa_filter' => 'boolean',
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    public function release(): BelongsTo
    {
        return $this->belongsTo(DtsenRelease::class, 'dtsen_release_id');
    }

    /** Pemakaian variabel ini pada permohonan — dipakai untuk mencegah penghapusan. */
    public function requestVariables(): HasMany
    {
        return $this->hasMany(DtsenRequestVariable::class, 'dtsen_variable_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Variabel yang boleh diminta pada level hak akses tertentu. */
    public function scopeUpToLevel($query, int $level)
    {
        return $query->where('level_minimal', '<=', $level);
    }

    /** Urutan katalog: set Keluarga lalu Anggota, kemudian urutan baris sesuai katalog. */
    public function scopeOrdered($query)
    {
        return $query->orderByRaw("CASE set_data WHEN 'keluarga' THEN 1 WHEN 'anggota' THEN 2 ELSE 3 END")
            ->orderBy('urutan')
            ->orderBy('kode');
    }

    /** Dua set data DTSEN yang terhubung lewat nomor kartu keluarga. */
    public static function setLabels(): array
    {
        return [
            'keluarga' => 'Set Keluarga',
            'anggota' => 'Set Anggota',
        ];
    }

    public static function setDescriptions(): array
    {
        return [
            'keluarga' => 'Satu baris per keluarga.',
            'anggota' => 'Satu baris per individu.',
        ];
    }

    public static function sensitivitasLabels(): array
    {
        return [
            'terbuka' => 'Terbuka',
            'quasi_identifier' => 'Quasi-identifier',
            'data_pribadi' => 'Data pribadi',
        ];
    }

    /**
     * Level hak akses minimal mengikuti sensitivitas: terbuka dapat dilayani sebagai
     * dataset kustom, quasi-identifier dapat mengidentifikasi orang bila digabung,
     * data pribadi hanya untuk permintaan BNBA yang disetujui.
     */
    public static function levelForSensitivitas(): array
    {
        return [
            'terbuka' => 2,
            'quasi_identifier' => 3,
            'data_pribadi' => 4,
        ];
    }

    /**
     * Kombinasi variabel yang diperlakukan sebagai permintaan data pribadi walau
     * masing-masing tercatat terbuka. Catatan katalog: RT/RW KTP yang digabung dengan
     * jenis kelamin (atau tanggal lahir) dapat mengidentifikasi orang — berlaku sampai
     * penandaan sensitivitas di kamus data disamakan.
     *
     * @return array<int, array{0: array<int, string>, 1: array<int, string>}> pasangan [salah satu dari, digabung dengan salah satu dari] (nama variabel set Anggota)
     */
    public static function kombinasiDataPribadi(): array
    {
        return [
            [['rt_ktp', 'rw_ktp'], ['jenis_kelamin', 'tanggal_lahir']],
        ];
    }

    /** Id variabel aktif per kombinasi di atas — dipakai form untuk menghitung level di browser. */
    public static function kombinasiDataPribadiIds(): array
    {
        $ids = static::where('set_data', 'anggota')->pluck('id', 'nama');

        return array_map(fn ($pair) => array_map(
            fn ($names) => collect($names)->map(fn ($n) => $ids[$n] ?? null)->filter()->values()->all(),
            $pair
        ), static::kombinasiDataPribadi());
    }

    public function getSetLabelAttribute(): string
    {
        return static::setLabels()[$this->set_data] ?? 'Lainnya';
    }

    public function getSensitivitasLabelAttribute(): string
    {
        return static::sensitivitasLabels()[$this->sensitivitas] ?? '-';
    }

    public function getLevelLabelAttribute(): string
    {
        return DtsenDataRequest::levelLabels()[$this->level_minimal] ?? ('Level ' . $this->level_minimal);
    }
}
