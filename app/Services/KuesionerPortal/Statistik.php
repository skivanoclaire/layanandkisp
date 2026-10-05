<?php

namespace App\Services\KuesionerPortal;

/**
 * Fungsi statistik murni untuk Subbab 3.3. Hasilnya untuk pemantauan di portal;
 * analisis final tesis tetap dijalankan di Jupyter dari data ekspor sehingga
 * kedua hasil dapat saling dicocokkan.
 */
class Statistik
{
    public static function rataRata(array $x): ?float
    {
        return $x ? array_sum($x) / count($x) : null;
    }

    /** Varians sampel (pembagi n − 1). */
    public static function varians(array $x): ?float
    {
        $n = count($x);
        if ($n < 2) {
            return null;
        }
        $m = array_sum($x) / $n;

        return array_sum(array_map(fn ($v) => ($v - $m) ** 2, $x)) / ($n - 1);
    }

    public static function pearson(array $x, array $y): ?float
    {
        $n = count($x);
        if ($n < 3 || $n !== count($y)) {
            return null;
        }
        $mx = array_sum($x) / $n;
        $my = array_sum($y) / $n;
        $sxy = $sxx = $syy = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $dx = $x[$i] - $mx;
            $dy = $y[$i] - $my;
            $sxy += $dx * $dy;
            $sxx += $dx * $dx;
            $syy += $dy * $dy;
        }
        if ($sxx == 0.0 || $syy == 0.0) {
            return null;
        }

        return $sxy / sqrt($sxx * $syy);
    }

    /**
     * Cronbach's alpha. $matriks = daftar baris responden, tiap baris daftar skor item (lengkap).
     */
    public static function cronbachAlpha(array $matriks): ?float
    {
        $n = count($matriks);
        if ($n < 2) {
            return null;
        }
        $k = count($matriks[0]);
        if ($k < 2) {
            return null;
        }

        $sumVarItem = 0.0;
        for ($j = 0; $j < $k; $j++) {
            $sumVarItem += static::varians(array_column($matriks, $j));
        }
        $varTotal = static::varians(array_map('array_sum', $matriks));
        if (! $varTotal) {
            return null;
        }

        return ($k / ($k - 1)) * (1 - $sumVarItem / $varTotal);
    }

    /**
     * Korelasi Pearson corrected item-total: item j terhadap total tanpa item j.
     *
     * @return array<int, float|null>
     */
    public static function korelasiItemTotalTerkoreksi(array $matriks): array
    {
        if (! $matriks) {
            return [];
        }
        $k = count($matriks[0]);
        $totals = array_map('array_sum', $matriks);
        $hasil = [];
        for ($j = 0; $j < $k; $j++) {
            $item = array_column($matriks, $j);
            $sisa = array_map(fn ($t, $v) => $t - $v, $totals, $item);
            $hasil[$j] = static::pearson($item, $sisa);
        }

        return $hasil;
    }

    /**
     * r tabel dua sisi pada α = 5% untuk n responden (df = n − 2).
     * t kritis dihitung dengan ekspansi Cornish-Fisher; galat < 0,001 untuk df ≥ 5.
     */
    public static function rTabel(int $n): ?float
    {
        $df = $n - 2;
        if ($df < 1) {
            return null;
        }
        $z = 1.959963985;
        $t = $z
            + ($z ** 3 + $z) / (4 * $df)
            + (5 * $z ** 5 + 16 * $z ** 3 + 3 * $z) / (96 * $df ** 2)
            + (3 * $z ** 7 + 19 * $z ** 5 + 17 * $z ** 3 - 15 * $z) / (384 * $df ** 3)
            + (79 * $z ** 9 + 776 * $z ** 7 + 1482 * $z ** 5 - 1920 * $z ** 3 - 945 * $z) / (92160 * $df ** 4);

        return $t / sqrt($df + $t * $t);
    }

    /** Peringkat dengan rata-rata untuk nilai kembar (mulai dari 1). */
    public static function peringkat(array $x): array
    {
        $idx = array_keys($x);
        usort($idx, fn ($a, $b) => $x[$a] <=> $x[$b]);
        $rank = [];
        $n = count($idx);
        for ($i = 0; $i < $n;) {
            $j = $i;
            while ($j + 1 < $n && $x[$idx[$j + 1]] == $x[$idx[$i]]) {
                $j++;
            }
            $r = ($i + $j) / 2.0 + 1;
            for ($m = $i; $m <= $j; $m++) {
                $rank[$idx[$m]] = $r;
            }
            $i = $j + 1;
        }
        ksort($rank);

        return $rank;
    }

    public static function spearman(array $x, array $y): ?float
    {
        return static::pearson(array_values(static::peringkat(array_values($x))), array_values(static::peringkat(array_values($y))));
    }

    /**
     * Uji Wilcoxon signed-rank dua sisi dengan pendekatan normal (koreksi nilai kembar,
     * tanpa koreksi kontinuitas). Selisih nol dibuang.
     *
     * @return array{n:int, w:float, z:float|null, p:float|null}
     */
    public static function wilcoxon(array $x, array $y): array
    {
        $d = [];
        foreach ($x as $i => $v) {
            $selisih = $v - $y[$i];
            if ($selisih != 0) {
                $d[] = $selisih;
            }
        }
        $n = count($d);
        if ($n === 0) {
            return ['n' => 0, 'w' => 0.0, 'z' => null, 'p' => null];
        }

        $rank = static::peringkat(array_map('abs', $d));
        $wPlus = $wMinus = 0.0;
        foreach ($d as $i => $v) {
            $v > 0 ? $wPlus += $rank[$i] : $wMinus += $rank[$i];
        }

        $koreksi = 0.0;
        foreach (array_count_values(array_map('strval', $rank)) as $t) {
            $koreksi += $t ** 3 - $t;
        }
        $mean = $n * ($n + 1) / 4;
        $var = $n * ($n + 1) * (2 * $n + 1) / 24 - $koreksi / 48;
        if ($var <= 0) {
            return ['n' => $n, 'w' => min($wPlus, $wMinus), 'z' => null, 'p' => null];
        }
        $z = ($wPlus - $mean) / sqrt($var);

        return [
            'n' => $n,
            'w' => min($wPlus, $wMinus),
            'z' => $z,
            'p' => min(1.0, 2 * (1 - static::normalCdf(abs($z)))),
        ];
    }

    /** CDF normal baku (Abramowitz & Stegun 7.1.26, galat < 1,5e-7). */
    public static function normalCdf(float $z): float
    {
        $x = abs($z) / M_SQRT2;
        $t = 1 / (1 + 0.3275911 * $x);
        $erf = 1 - ((((1.061405429 * $t - 1.453152027) * $t + 1.421413741) * $t - 0.284496736) * $t + 0.254829592) * $t * exp(-$x * $x);

        return $z >= 0 ? 0.5 * (1 + $erf) : 0.5 * (1 - $erf);
    }

    /** Kelas rata-rata skor 1–5 dengan interval 0,80 (Subbab 3.3.3). */
    public static function kelasRataRata(?float $m): ?string
    {
        if ($m === null) {
            return null;
        }

        return match (true) {
            $m <= 1.80 => 'Sangat rendah',
            $m <= 2.60 => 'Rendah',
            $m <= 3.40 => 'Sedang',
            $m <= 4.20 => 'Tinggi',
            default => 'Sangat tinggi',
        };
    }

    /** Interpretasi tingkat kesesuaian (Tabel 3.4). */
    public static function interpretasiTk(?float $tk): ?string
    {
        if ($tk === null) {
            return null;
        }

        return match (true) {
            $tk < 80 => 'Belum memenuhi harapan',
            $tk <= 100 => 'Memenuhi, perlu perbaikan',
            default => 'Memenuhi/melampaui harapan',
        };
    }
}
