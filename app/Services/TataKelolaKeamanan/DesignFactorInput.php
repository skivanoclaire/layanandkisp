<?php

namespace App\Services\TataKelolaKeamanan;

use Illuminate\Validation\ValidationException;

/**
 * Membersihkan dan memvalidasi isian kuesioner design factors.
 * Dipakai oleh halaman admin (entri manual / nilai akhir FGD) dan halaman responden.
 */
class DesignFactorInput
{
    /**
     * @param  array  $input  isian mentah per kode DF (request input "nilai")
     * @return array<string, array>  nilai bersih per kode DF yang diisi
     */
    public function bersihkanSemua(array $input): array
    {
        $hasil = [];
        foreach (config('tata_kelola_keamanan.design_factors') as $kode => $df) {
            if (! array_key_exists($kode, $input) || $input[$kode] === null) {
                continue;
            }
            $hasil[$kode] = $this->bersihkan($kode, $df, (array) $input[$kode]);
        }

        return $hasil;
    }

    public function bersihkan(string $kode, array $df, array $input): array
    {
        $jumlahButir = count($df['butir']);

        if ($df['tipe'] === 'pilihan') {
            if (! isset($input['pilihan']) || $input['pilihan'] === '') {
                return [];
            }
            $i = (int) $input['pilihan'];

            return ($i >= 0 && $i < $jumlahButir) ? ['pilihan' => $i] : [];
        }

        if ($df['tipe'] === 'risiko') {
            [$min, $max] = $df['skala'];
            $hasil = [];
            for ($i = 0; $i < $jumlahButir; $i++) {
                $hasil['dampak'][$i] = $this->dalamRentang($input['dampak'][$i] ?? null, $min, $max);
                $hasil['kemungkinan'][$i] = $this->dalamRentang($input['kemungkinan'][$i] ?? null, $min, $max);
            }

            return $hasil;
        }

        $nilai = [];
        for ($i = 0; $i < $jumlahButir; $i++) {
            $v = $input[$i] ?? null;
            $nilai[$i] = $df['tipe'] === 'persen'
                ? $this->dalamRentang($v, 0, 100)
                : $this->dalamRentang($v, $df['skala'][0], $df['skala'][1]);
        }

        if ($df['tipe'] === 'persen' && array_filter($nilai, fn ($v) => $v !== null)) {
            $total = array_sum($nilai);
            if ($total !== 100) {
                throw ValidationException::withMessages([
                    "nilai.$kode" => "{$kode} ({$df['nama']}): jumlah persentase harus 100, sekarang {$total}.",
                ]);
            }
        }

        return $nilai;
    }

    /** Apakah isian DF sudah lengkap (semua butir terisi). */
    public function lengkap(array $df, ?array $nilai): bool
    {
        if (! $nilai) {
            return false;
        }
        if ($df['tipe'] === 'pilihan') {
            return isset($nilai['pilihan']);
        }
        if ($df['tipe'] === 'risiko') {
            return ! in_array(null, $nilai['dampak'] ?? [null], true) && ! in_array(null, $nilai['kemungkinan'] ?? [null], true);
        }

        return count($nilai) === count($df['butir']) && ! in_array(null, $nilai, true);
    }

    private function dalamRentang($v, int $min, int $max): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        $v = (int) $v;

        return ($v >= $min && $v <= $max) ? $v : null;
    }
}
