<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Helper penamaan berkas unggahan yang aman.
 *
 * Latar belakang (insiden 2026-11-01): ekstensi dari getClientOriginalExtension()
 * sepenuhnya dikendalikan pengirim. Validasi `mimes:` hanya memeriksa ISI berkas,
 * sehingga gambar JPEG yang disisipi kode PHP bisa lolos lalu disimpan sebagai
 * .php / .php56 / .shtml (webshell). Jangan pernah menyusun nama berkas dari
 * ekstensi atau nama asli klien tanpa melalui helper ini.
 */
final class SafeUpload
{
    /**
     * Ekstensi aman: ekstensi klien hanya dipakai bila ada di allowlist,
     * jika tidak dipakai ekstensi hasil deteksi isi berkas (bila ada di allowlist),
     * selain itu 'bin' (tidak akan pernah dieksekusi web server).
     *
     * @param  string[]  $allowed  sama dengan daftar pada aturan validasi mimes:
     */
    public static function extension(UploadedFile $file, array $allowed, string $fallback = 'bin'): string
    {
        $allowed = array_map('strtolower', $allowed);

        $client = strtolower((string) $file->getClientOriginalExtension());
        if ($client !== '' && in_array($client, $allowed, true)) {
            return $client;
        }

        $guessed = strtolower((string) $file->extension());
        if ($guessed !== '' && in_array($guessed, $allowed, true)) {
            return $guessed;
        }

        return $fallback;
    }

    /**
     * Nama dasar (tanpa ekstensi) dari nama asli klien, hanya [A-Za-z0-9_-],
     * dipotong agar tidak terlalu panjang.
     */
    public static function baseName(UploadedFile $file, int $max = 80, string $default = 'berkas'): string
    {
        $name = pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = preg_replace('/[^A-Za-z0-9_-]+/', '_', $name);
        $name = trim(substr((string) $name, 0, $max), '_');

        return $name !== '' ? $name : $default;
    }
}
