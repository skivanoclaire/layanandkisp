<?php

namespace App\Models\MasterArsitektur;

use App\Models\UnitKerja;
use App\Services\MasterArsitektur\Skema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu entri Master Data Arsitektur. Susunan kolom tiap "jenis" ada di
 * config/master_arsitektur.php.
 */
class Item extends Model
{
    protected $table = 'master_arsitektur_items';

    protected $fillable = [
        'jenis', 'kode', 'nama', 'status', 'unit_kerja_id', 'unit_kerja_teks',
        'ref_1', 'ref_2', 'ref_3', 'tahun', 'atribut',
    ];

    protected $casts = [
        'atribut' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Item $item) {
            $item->kunci = self::hitungKunci($item->jenis, $item->nilaiKolom());
        });
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    /** Nama perangkat daerah: dari Master Data Instansi bila sudah terpetakan. */
    public function getNamaUnitKerjaAttribute(): ?string
    {
        return $this->unitKerja?->nama ?? $this->unit_kerja_teks;
    }

    public function getLabelJenisAttribute(): string
    {
        return Skema::adaJenis($this->jenis) ? Skema::label($this->jenis) : $this->jenis;
    }

    /** Nilai satu kolom config, baik kolom inti maupun atribut. */
    public function nilai(string $kolom): mixed
    {
        if ($kolom === 'unit_kerja') {
            return $this->nama_unit_kerja;
        }

        return in_array($kolom, Skema::INTI, true)
            ? $this->getAttribute($kolom)
            : ($this->atribut[$kolom] ?? null);
    }

    /** Seluruh nilai menurut kunci kolom config (unit_kerja = teks sumber). */
    public function nilaiKolom(): array
    {
        return array_merge($this->atribut ?? [], [
            'kode' => $this->kode,
            'nama' => $this->nama,
            'status' => $this->status,
            'unit_kerja' => $this->unit_kerja_teks,
            'ref_1' => $this->ref_1,
            'ref_2' => $this->ref_2,
            'ref_3' => $this->ref_3,
            'tahun' => $this->tahun,
        ]);
    }

    /**
     * Sidik baris dari kolom kunci jenisnya. Bila seluruh kolom kunci kosong,
     * nama dipakai agar baris tanpa kode tidak saling menimpa.
     */
    public static function hitungKunci(string $jenis, array $nilai): string
    {
        $norm = fn ($v) => mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $v)));

        $bagian = array_map(fn ($k) => $norm($nilai[$k] ?? ''), Skema::kolomKunci($jenis));

        if (implode('', $bagian) === '') {
            $bagian = [$norm($nilai['nama'] ?? '')];
        }

        return sha1($jenis . '|' . implode('|', $bagian));
    }
}
