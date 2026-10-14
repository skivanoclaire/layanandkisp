<?php

namespace App\Services\TataKelolaKeamanan;

/**
 * Aturan keputusan modul. Seluruh ambang dibaca dari config/tata_kelola_keamanan.php.
 */
class Aturan
{
    /** Skor kepentingan objektif -> target tingkat kapabilitas. */
    public static function targetLevel(int $skor): int
    {
        $tabel = config('tata_kelola_keamanan.aturan.target_level');
        krsort($tabel);
        foreach ($tabel as $batas => $level) {
            if ($skor >= $batas) {
                return $level;
            }
        }

        return config('tata_kelola_keamanan.aturan.target_level_minimum', 1);
    }

    /** Persentase aktivitas terpenuhi -> rating N/P/L/F. */
    public static function rating(float $persen): string
    {
        foreach (config('tata_kelola_keamanan.aturan.rating') as $rating => $batasAtas) {
            if ($persen <= $batasAtas) {
                return $rating;
            }
        }

        return 'F';
    }

    /** Butir disepakati bila minimal 2/3 pakar yang menilai menyatakan setuju. */
    public static function disepakati(int $setuju, int $jumlahPenilai): bool
    {
        if ($jumlahPenilai === 0) {
            return false;
        }
        $p = config('tata_kelola_keamanan.aturan.kesepakatan_pembilang');
        $q = config('tata_kelola_keamanan.aturan.kesepakatan_penyebut');

        // setuju / penilai >= p / q, dihitung tanpa pecahan desimal
        return $setuju * $q >= $p * $jumlahPenilai;
    }

    public static function labelRating(string $rating): string
    {
        return [
            'N' => 'Not achieved',
            'P' => 'Partially achieved',
            'L' => 'Largely achieved',
            'F' => 'Fully achieved',
        ][$rating] ?? $rating;
    }
}
