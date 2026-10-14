<?php

namespace App\Services\TataKelolaKeamanan;

/**
 * Akses terjemahan dan penjelasan istilah (config/tata_kelola_keamanan_istilah.php)
 * serta judul resmi klausul/kontrol dan ketentuan regulasi.
 *
 * Catatan: kode seperti "A.5.1", "K6.1.2", "APO13.01", dan "PP95-40.1" mengandung titik,
 * sehingga tidak boleh dibaca dengan notasi titik config('x.y.A.5.1'). Ambil array-nya
 * terlebih dahulu, lalu indeks dengan kode.
 */
class Istilah
{
    private const K = 'tata_kelola_keamanan_istilah';

    private static function ambil(string $kunci, string $kode)
    {
        return config($kunci, [])[$kode] ?? null;
    }

    /** @return array{nama: string, nama_id: ?string, penjelasan: ?string} */
    public static function objektif(string $kode): array
    {
        $id = self::ambil(self::K.'.objektif', $kode);

        return [
            'nama' => self::ambil('tata_kelola_keamanan.objektif', $kode)['nama'] ?? $kode,
            'nama_id' => $id[0] ?? null,
            'penjelasan' => $id[1] ?? null,
        ];
    }

    public static function praktik(string $kode): ?string
    {
        return self::ambil(self::K.'.praktik', $kode);
    }

    public static function domain(string $kode): ?string
    {
        return self::ambil(self::K.'.domain', $kode);
    }

    public static function regulasi(string $kode): string
    {
        return self::ambil('tata_kelola_keamanan.regulasi', $kode) ?? $kode;
    }

    public static function dfNama(string $kode): ?string
    {
        return config(self::K.".design_factors.$kode.nama");
    }

    public static function dfButir(string $kode, int $i): ?string
    {
        return config(self::K.".design_factors.$kode.butir.$i");
    }

    public static function dfPenjelasanButir(string $kode, int $i): ?string
    {
        return config("tata_kelola_keamanan.design_factors.$kode.penjelasan_butir.$i")
            ?? config(self::K.".design_factors.$kode.penjelasan_butir.$i");
    }

    /** Judul resmi (bahasa Inggris) klausul atau kontrol. */
    public static function isoJudul(string $kode): string
    {
        return str_starts_with($kode, 'K')
            ? (self::ambil('tata_kelola_keamanan.iso_klausul', $kode) ?? '')
            : (self::ambil('tata_kelola_keamanan.iso_kontrol', $kode) ?? '');
    }

    /** Terjemahan Indonesia klausul atau kontrol. */
    public static function isoId(string $kode): ?string
    {
        $kunci = str_starts_with($kode, 'K') ? self::K.'.iso_klausul' : self::K.'.iso_kontrol';

        return self::ambil($kunci, $kode)[0] ?? null;
    }

    /**
     * Informasi klausul (kode "K…") atau kontrol Annex A (kode "A.…").
     *
     * @return array{label: string, id: ?string, penjelasan: ?string, baru: bool}
     */
    public static function iso(string $kode): array
    {
        $klausul = str_starts_with($kode, 'K');
        $id = self::ambil($klausul ? self::K.'.iso_klausul' : self::K.'.iso_kontrol', $kode);

        return [
            'label' => $klausul ? 'Klausul '.substr($kode, 1).' '.self::isoJudul($kode) : $kode.' '.self::isoJudul($kode),
            'id' => $id[0] ?? null,
            'penjelasan' => $id[1] ?? null,
            'baru' => ! $klausul && in_array($kode, config('tata_kelola_keamanan.iso_kontrol_baru', []), true),
        ];
    }

    /** Label ringkas dwibahasa: "A.8.7 Protection against malware (Perlindungan terhadap malware)". */
    public static function isoRingkas(string $kode): string
    {
        $i = self::iso($kode);

        return trim($i['label'].($i['id'] ? ' ('.$i['id'].')' : ''));
    }
}
