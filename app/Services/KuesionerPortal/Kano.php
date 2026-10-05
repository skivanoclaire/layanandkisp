<?php

namespace App\Services\KuesionerPortal;

/**
 * Klasifikasi Kano: Tabel 2.3 (Berger dkk, 1993), aturan penetapan kategori,
 * uji Fong (Persamaan 2.9), dan koefisien better-worse (Persamaan 2.7–2.8).
 */
class Kano
{
    // Baris = jawaban fungsional 1–5, kolom = jawaban disfungsional 1–5.
    private const TABEL = [
        1 => [1 => 'Q', 2 => 'A', 3 => 'A', 4 => 'A', 5 => 'O'],
        2 => [1 => 'R', 2 => 'I', 3 => 'I', 4 => 'I', 5 => 'M'],
        3 => [1 => 'R', 2 => 'I', 3 => 'I', 4 => 'I', 5 => 'M'],
        4 => [1 => 'R', 2 => 'I', 3 => 'I', 4 => 'I', 5 => 'M'],
        5 => [1 => 'R', 2 => 'R', 3 => 'R', 4 => 'R', 5 => 'Q'],
    ];

    public const NAMA = [
        'A' => 'Attractive',
        'O' => 'One-dimensional',
        'M' => 'Must-be',
        'I' => 'Indifferent',
        'R' => 'Reverse',
        'Q' => 'Questionable',
    ];

    public static function kategori(?int $fungsional, ?int $disfungsional): ?string
    {
        return self::TABEL[$fungsional][$disfungsional] ?? null;
    }

    /**
     * Ringkasan satu atribut dari daftar kategori jawaban responden (sudah dikonversi).
     * Jawaban Q dihitung untuk pelaporan, tetapi dikeluarkan dari n dan penetapan kategori.
     */
    public static function ringkas(array $kategoriJawaban): array
    {
        $f = array_fill_keys(['A', 'O', 'M', 'I', 'R', 'Q'], 0);
        foreach ($kategoriJawaban as $k) {
            if ($k !== null) {
                $f[$k]++;
            }
        }
        $n = $f['A'] + $f['O'] + $f['M'] + $f['I'] + $f['R'];
        $total = $n + $f['Q'];

        if ($n === 0) {
            return [
                'frekuensi' => $f, 'n' => 0, 'proporsi_q' => $total ? 1.0 : null,
                'kategori' => null, 'kategori_tampil' => '-', 'campuran' => false,
                'fong' => null, 'better' => null, 'worse' => null,
            ];
        }

        // Aturan Berger: bila A+O+M > I+R(+Q) pilih terbanyak dari A/O/M, selain itu dari I/R.
        $kandidat = ($f['A'] + $f['O'] + $f['M']) > ($f['I'] + $f['R'])
            ? ['M', 'O', 'A']
            : ['I', 'R'];
        $kategori = static::terbanyak($f, $kandidat);

        // Uji Fong: kategori hasil aturan Berger dibandingkan dengan kategori lain yang
        // frekuensinya tertinggi di antara A, O, M, I, R.
        $kedua = static::terbanyak($f, array_values(array_diff(['M', 'O', 'A', 'I', 'R'], [$kategori])));
        $a = $f[$kategori];
        $b = $f[$kedua];
        $batas = 1.65 * sqrt(($a + $b) * (2 * $n - $a - $b) / (2 * $n));
        $tegas = abs($a - $b) >= $batas;

        $penyebut = $f['A'] + $f['O'] + $f['M'] + $f['I'];

        return [
            'frekuensi' => $f,
            'n' => $n,
            'proporsi_q' => $total ? $f['Q'] / $total : null,
            'kategori' => $kategori,
            'kategori_kedua' => $kedua,
            'kategori_tampil' => $tegas ? $kategori : $kategori . '/' . $kedua,
            'campuran' => ! $tegas,
            'fong' => ['selisih' => abs($a - $b), 'batas' => $batas, 'tegas' => $tegas],
            'better' => $penyebut ? ($f['A'] + $f['O']) / $penyebut : null,
            'worse' => $penyebut ? -($f['O'] + $f['M']) / $penyebut : null,
        ];
    }

    /** Bila frekuensi sama, urutan prioritas M > O > A > I > R (dampak ke ketidakpuasan lebih besar). */
    private static function terbanyak(array $f, array $kandidat): string
    {
        $urutan = ['M', 'O', 'A', 'I', 'R'];
        usort($kandidat, fn ($x, $y) => [$f[$y], array_search($x, $urutan)] <=> [$f[$x], array_search($y, $urutan)]);

        return $kandidat[0];
    }
}
