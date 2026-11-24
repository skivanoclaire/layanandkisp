<?php

namespace App\Support;

/**
 * Jenis kelamin pengguna dan peserta presensi.
 *
 * Sumber (urutan prioritas saat sinkron SIMPEG):
 *   1. Field jenis kelamin dari respons SIMPEG (bila ada)
 *   2. Digit ke-15 NIP: 1 = laki-laki, 2 = perempuan
 *   3. Digit tanggal lahir NIK (digit ke-7 s.d. 8): DD+40 = perempuan
 */
class JenisKelamin
{
    public const LAKI_LAKI = 'laki-laki';
    public const PEREMPUAN = 'perempuan';

    public const PILIHAN = [
        self::LAKI_LAKI => 'Laki-laki',
        self::PEREMPUAN => 'Perempuan',
    ];

    /** Kunci yang mungkin dipakai SIMPEG untuk jenis kelamin. */
    private const KUNCI_SIMPEG = ['jenis_kelamin', 'jenisKelamin', 'kelamin', 'jk', 'gender', 'sex'];

    public static function normalisasi(mixed $nilai): ?string
    {
        if (!is_scalar($nilai)) {
            return null;
        }

        $teks = mb_strtolower(trim((string) $nilai));

        return match (true) {
            in_array($teks, ['l', '1', 'laki-laki', 'laki laki', 'lakilaki', 'pria', 'm', 'male'], true) => self::LAKI_LAKI,
            in_array($teks, ['p', '2', 'perempuan', 'wanita', 'f', 'female'], true) => self::PEREMPUAN,
            default => null,
        };
    }

    public static function label(?string $nilai): ?string
    {
        return self::PILIHAN[$nilai] ?? null;
    }

    /** Digit ke-15 NIP (indeks 14): 1 laki-laki, 2 perempuan. */
    public static function dariNip(?string $nip): ?string
    {
        $digit = preg_replace('/\D+/', '', (string) $nip);

        if (strlen($digit) !== 18) {
            return null;
        }

        return match ($digit[14]) {
            '1' => self::LAKI_LAKI,
            '2' => self::PEREMPUAN,
            default => null,
        };
    }

    /** Digit tanggal lahir NIK: tanggal + 40 berarti perempuan. */
    public static function dariNik(?string $nik): ?string
    {
        $digit = preg_replace('/\D+/', '', (string) $nik);

        if (strlen($digit) !== 16) {
            return null;
        }

        return (int) substr($digit, 6, 2) > 40 ? self::PEREMPUAN : self::LAKI_LAKI;
    }

    /** Field jenis kelamin langsung dari respons SIMPEG, bila gateway mengirimkannya. */
    public static function dariSimpeg(array $data): ?string
    {
        foreach (self::KUNCI_SIMPEG as $kunci) {
            if (array_key_exists($kunci, $data) && ($hasil = self::normalisasi($data[$kunci])) !== null) {
                return $hasil;
            }
        }

        return null;
    }

    /** Gabungan semua sumber: data SIMPEG dulu, lalu NIP, lalu NIK. */
    public static function dariData(array $simpeg = [], ?string $nip = null, ?string $nik = null): ?string
    {
        return self::dariSimpeg($simpeg) ?? self::dariNip($nip ?? ($simpeg['nip'] ?? null)) ?? self::dariNik($nik ?? ($simpeg['nik'] ?? null));
    }
}
