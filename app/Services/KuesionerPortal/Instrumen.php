<?php

namespace App\Services\KuesionerPortal;

/**
 * Akses terstruktur ke instrumen pada config/kuesioner_portal.php.
 */
class Instrumen
{
    /**
     * 21 atribut E-GovQual: kode => [dimensi, nama_dimensi, kinerja, fungsional, disfungsional].
     */
    public static function atribut(): array
    {
        $hasil = [];
        foreach (config('kuesioner_portal.dimensi') as $kodeDimensi => $dimensi) {
            foreach ($dimensi['atribut'] as $kode => $atribut) {
                $hasil[$kode] = $atribut + [
                    'dimensi' => $kodeDimensi,
                    'nama_dimensi' => $dimensi['nama'],
                ];
            }
        }

        return $hasil;
    }

    public static function kodeAtribut(): array
    {
        return array_keys(static::atribut());
    }

    public static function kodeKepuasan(): array
    {
        return array_keys(config('kuesioner_portal.kepuasan'));
    }

    /**
     * Atribut yang kinerjanya hanya dinilai responden yang pernah menghubungi petugas.
     */
    public static function kodeKinerjaBersyarat(): array
    {
        $hasil = [];
        foreach (config('kuesioner_portal.dimensi') as $dimensi) {
            if (! empty($dimensi['kinerja_hanya_jika_pernah_hubungi_petugas'])) {
                $hasil = array_merge($hasil, array_keys($dimensi['atribut']));
            }
        }

        return $hasil;
    }

    /**
     * kode dimensi => daftar kode atribut
     */
    public static function atributPerDimensi(): array
    {
        return collect(config('kuesioner_portal.dimensi'))
            ->map(fn ($d) => array_keys($d['atribut']))
            ->all();
    }
}
