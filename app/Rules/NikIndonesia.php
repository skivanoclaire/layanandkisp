<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validasi NIK (Nomor Induk Kependudukan) sesuai struktur baku Dukcapil.
 *
 * Format: PP KK CC DDMMYY SSSS (16 digit)
 *   PP     = kode provinsi
 *   KK     = kode kabupaten/kota
 *   CC     = kode kecamatan
 *   DDMMYY = tanggal lahir; DD ditambah 40 untuk perempuan
 *   SSSS   = nomor urut penerbitan
 *
 * Cek panjang saja (digits:16) tidak cukup: NIP PNS 18 digit yang terpotong
 * jadi 16 digit tetap lolos. Kelas ini menolaknya lewat cek struktur —
 * pada potongan NIP, posisi bulan selalu terisi dua digit pertama tahun TMT
 * ("19"/"20") sehingga mustahil valid.
 */
class NikIndonesia implements ValidationRule
{
    /** Kode provinsi Indonesia (termasuk 4 provinsi baru di Papua). */
    private const PROVINCE_CODES = [
        '11', '12', '13', '14', '15', '16', '17', '18', '19', '21',
        '31', '32', '33', '34', '35', '36',
        '51', '52', '53',
        '61', '62', '63', '64', '65',
        '71', '72', '73', '74', '75', '76',
        '81', '82',
        '91', '92', '93', '94', '95', '96',
    ];

    private const DAYS_IN_MONTH = [
        1 => 31, 2 => 29, 3 => 31, 4 => 30, 5 => 31, 6 => 30,
        7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31,
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $raw = is_scalar($value) ? trim((string) $value) : '';

        if ($raw === '') {
            $fail('NIK wajib diisi.');
            return;
        }

        if (!preg_match('/^\d+$/', $raw)) {
            $fail('NIK hanya boleh berisi angka, tanpa spasi, titik, atau tanda baca lain.');
            return;
        }

        // Dicek lebih dulu supaya pesan errornya spesifik. Tidak mungkin false
        // positive: NIK yang strukturnya benar selalu punya digit 9-12 berupa
        // MMYY (maksimal 1299), sementara pola NIP butuh tahun TMT >= 1950.
        if (self::looksLikeNip($raw)) {
            $fail('Yang Anda masukkan sepertinya NIP, bukan NIK. Isi dengan NIK 16 digit yang tertera di KTP.');
            return;
        }

        if (strlen($raw) !== 16) {
            $fail('NIK harus tepat 16 digit angka (Anda memasukkan ' . strlen($raw) . ' digit).');
            return;
        }

        if (preg_match('/^(\d)\1{15}$/', $raw)) {
            $fail('NIK tidak valid: semua digitnya sama.');
            return;
        }

        if (!in_array(substr($raw, 0, 2), self::PROVINCE_CODES, true)) {
            $fail('NIK tidak valid: dua digit pertama (' . substr($raw, 0, 2) . ') bukan kode provinsi yang dikenal.');
            return;
        }

        if (substr($raw, 2, 2) === '00') {
            $fail('NIK tidak valid: kode kabupaten/kota (digit ke-3 dan ke-4) tidak boleh 00.');
            return;
        }

        if (substr($raw, 4, 2) === '00') {
            $fail('NIK tidak valid: kode kecamatan (digit ke-5 dan ke-6) tidak boleh 00.');
            return;
        }

        $day   = (int) substr($raw, 6, 2);
        $month = (int) substr($raw, 8, 2);

        if ($month < 1 || $month > 12) {
            $fail('NIK tidak valid: digit ke-9 dan ke-10 harus berupa bulan lahir (01-12), bukan "' . substr($raw, 8, 2) . '".');
            return;
        }

        // DD ditambah 40 menandakan jenis kelamin perempuan.
        $realDay = $day > 40 ? $day - 40 : $day;

        if ($realDay < 1 || $realDay > self::DAYS_IN_MONTH[$month]) {
            $fail('NIK tidak valid: digit ke-7 dan ke-8 harus berupa tanggal lahir (01-31, atau +40 untuk perempuan).');
            return;
        }

        if (substr($raw, 12, 4) === '0000') {
            $fail('NIK tidak valid: empat digit terakhir (nomor urut) tidak boleh 0000.');
        }
    }

    /**
     * Apakah nilai ini berpola NIP PNS (18 digit: YYYYMMDD + YYYYMM + G + NNN),
     * termasuk potongan 16 digit pertamanya akibat maxlength pada input?
     */
    public static function looksLikeNip(string $digits): bool
    {
        if (!preg_match('/^\d{16,18}$/', $digits)) {
            return false;
        }

        $year    = (int) substr($digits, 0, 4);
        $month   = (int) substr($digits, 4, 2);
        $day     = (int) substr($digits, 6, 2);
        $tmtYear = (int) substr($digits, 8, 4);

        return $year >= 1930
            && $year <= (int) date('Y') - 15
            && $month >= 1 && $month <= 12
            && checkdate($month, $day, $year)
            && $tmtYear >= 1950
            && $tmtYear <= (int) date('Y') + 5;
    }

    /** Helper untuk pemakaian di luar validator (endpoint cek, audit data). */
    public static function isValid(?string $nik): bool
    {
        if ($nik === null) {
            return false;
        }

        $failed = false;
        (new self())->validate('nik', $nik, function () use (&$failed) {
            $failed = true;
        });

        return !$failed;
    }
}
