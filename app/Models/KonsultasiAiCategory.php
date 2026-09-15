<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Master kategori knowledge base Konsultasi SPBE AI.
 * Nilainya dipakai sebagai isi kolom `kategori` pada dokumen dan pertanyaan contoh.
 */
class KonsultasiAiCategory extends Model
{
    public const TIPE_DOKUMEN = 'dokumen';
    public const TIPE_FAQ = 'faq';

    protected $table = 'konsultasi_ai_categories';

    protected $fillable = [
        'tipe',
        'nama',
        'deskripsi',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan'    => 'integer',
    ];

    /**
     * @return array<string, string>
     */
    public static function tipeLabels(): array
    {
        return [
            self::TIPE_DOKUMEN => 'Dokumen Dasar',
            self::TIPE_FAQ     => 'Pertanyaan Contoh',
        ];
    }

    public function getTipeLabelAttribute(): string
    {
        return self::tipeLabels()[$this->tipe] ?? $this->tipe;
    }

    public function scopeTipe($query, string $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }

    /**
     * Daftar nama kategori satu tipe, urut tampil.
     *
     * @return Collection<int, string>
     */
    public static function namaUntuk(string $tipe, bool $hanyaAktif = true): Collection
    {
        return static::query()
            ->tipe($tipe)
            ->when($hanyaAktif, fn ($q) => $q->active())
            ->urut()
            ->pluck('nama');
    }

    /**
     * Berapa banyak dokumen/pertanyaan yang memakai kategori ini.
     */
    public function jumlahPemakaian(): int
    {
        $model = $this->tipe === self::TIPE_DOKUMEN
            ? KonsultasiAiDocument::class
            : KonsultasiAiFaq::class;

        return $model::where('kategori', $this->nama)->count();
    }
}
