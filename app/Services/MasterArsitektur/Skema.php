<?php

namespace App\Services\MasterArsitektur;

use InvalidArgumentException;

/**
 * Pembaca config/master_arsitektur.php: kategori, jenis, dan kolom.
 */
class Skema
{
    /** Kunci kolom yang disimpan sebagai kolom tabel, bukan di JSON "atribut". */
    public const INTI = ['kode', 'nama', 'status', 'unit_kerja', 'ref_1', 'ref_2', 'ref_3', 'tahun'];

    public static function kategori(?string $kunci = null): array
    {
        $semua = config('master_arsitektur.kategori');

        if ($kunci === null) {
            return $semua;
        }

        return $semua[$kunci] ?? throw new InvalidArgumentException("Kategori [$kunci] tidak dikenal.");
    }

    public static function jenis(?string $kunci = null): array
    {
        $semua = config('master_arsitektur.jenis');

        if ($kunci === null) {
            return $semua;
        }

        return $semua[$kunci] ?? throw new InvalidArgumentException("Jenis data [$kunci] tidak dikenal.");
    }

    public static function adaJenis(?string $kunci): bool
    {
        return $kunci !== null && array_key_exists($kunci, config('master_arsitektur.jenis'));
    }

    public static function kolom(string $jenis): array
    {
        return self::jenis($jenis)['kolom'];
    }

    public static function label(string $jenis): string
    {
        return self::jenis($jenis)['label'];
    }

    /** Kategori tempat suatu jenis berada. */
    public static function kategoriDari(string $jenis): string
    {
        foreach (self::kategori() as $kunci => $kategori) {
            if (in_array($jenis, $kategori['jenis'], true)) {
                return $kunci;
            }
        }

        throw new InvalidArgumentException("Jenis data [$jenis] tidak masuk kategori mana pun.");
    }

    /** Kolom penentu baris yang sama saat impor ulang. */
    public static function kolomKunci(string $jenis): array
    {
        return self::jenis($jenis)['kunci'] ?? ['kode', 'nama'];
    }

    /** Opsi baku kolom bertipe "pilihan"; [] untuk kolom isian bebas. */
    public static function opsi(array $kolom): array
    {
        return ($kolom['tipe'] ?? 'text') === 'pilihan' ? ($kolom['opsi'] ?? []) : [];
    }

    /**
     * Samakan penulisan nilai dengan opsi baku ("as-is " => "As-is").
     * Nilai yang tidak cocok dengan opsi mana pun dikembalikan apa adanya.
     */
    public static function bakukan(array $kolom, ?string $nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        $normal = fn (string $v) => mb_strtolower(preg_replace('/\s+/u', ' ', trim($v)));

        foreach (self::opsi($kolom) as $opsi) {
            if ($normal($opsi) === $normal($nilai)) {
                return $opsi;
            }
        }

        return $nilai;
    }

    /** Judul kolom pada Excel (dipakai saat ekspor). */
    public static function judulExcel(array $kolom): string
    {
        return ((array) ($kolom['header'] ?? $kolom['label']))[0];
    }

    /**
     * Peta judul kolom Excel (ternormalisasi) => kunci kolom.
     * Judul lengkap didahulukan; bentuk pendek (tanpa keterangan dalam kurung)
     * hanya dipakai bila tidak bertabrakan dengan judul lengkap kolom lain.
     */
    public static function petaJudul(string $jenis): array
    {
        $peta = [];

        foreach ([false, true] as $pendek) {
            foreach (self::kolom($jenis) as $kunci => $kolom) {
                $judul = array_merge((array) ($kolom['header'] ?? []), [$kolom['label']]);
                foreach ($judul as $j) {
                    $n = self::normalJudul($j, $pendek);
                    if ($n !== '' && ! isset($peta[$n])) {
                        $peta[$n] = $kunci;
                    }
                }
            }
        }

        return $peta;
    }

    /** Cari kunci kolom untuk satu judul kolom dari berkas. */
    public static function cocokkanJudul(array $peta, mixed $judul): ?string
    {
        if (! is_scalar($judul)) {
            return null;
        }

        return $peta[self::normalJudul((string) $judul)]
            ?? $peta[self::normalJudul((string) $judul, true)]
            ?? null;
    }

    /**
     * "→ RAB Level 2 (Dependency)" => "rab level 2 (dependency)";
     * dengan $pendek => "rab level 2".
     */
    public static function normalJudul(string $judul, bool $pendek = false): string
    {
        $judul = str_replace(['→', '←'], ' ', $judul);

        if ($pendek) {
            $judul = preg_split('/\s*(\(|\[|--|\s-\s)/u', $judul, 2)[0];
        }

        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $judul)));
    }
}
