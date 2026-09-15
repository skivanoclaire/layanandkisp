<?php

namespace App\Services\Dtsen;

use App\Models\User;

/**
 * Sumber tunggal identitas pemohon yang diambil dari profil pengguna.
 *
 * Dipakai form DTSEN agar kolom yang sudah ada di profil tidak perlu diketik ulang:
 * field yang profilnya sudah terisi dikunci di tampilan DAN ditimpa di sisi server,
 * sehingga nilai yang tersimpan selalu konsisten dengan profil meski input di
 * browser diubah paksa.
 *
 * Field yang profilnya masih kosong tetap dibiarkan terbuka supaya pemohon tidak
 * terhalang — misalnya jabatan yang belum disinkronkan dari SIMPEG.
 */
class ProfilPemohon
{
    /** Field identitas yang dikenal, beserta asal datanya di profil. */
    public const FIELDS = ['nama', 'nip', 'jabatan', 'telepon', 'email', 'unit_kerja_id'];

    public function __construct(private readonly User $user)
    {
    }

    public static function untuk(User $user): self
    {
        return new self($user);
    }

    public function user(): User
    {
        return $this->user;
    }

    public function nilai(string $field): string|int|null
    {
        return match ($field) {
            'nama' => $this->user->name,
            'nip' => $this->user->nip,
            // Jabatan disinkronkan dari SIMPEG oleh admin, bukan diisi sendiri.
            'jabatan' => $this->user->jabatan?->nama_jabatan,
            'telepon' => $this->user->phone,
            'email' => $this->user->email,
            'unit_kerja_id' => $this->user->unit_kerja_id,
            default => null,
        };
    }

    /** Profil sudah punya data untuk field ini, sehingga boleh dikunci. */
    public function terisi(string $field): bool
    {
        return filled($this->nilai($field));
    }

    public function namaUnitKerja(): ?string
    {
        return $this->user->unitKerja?->nama;
    }

    /**
     * Nilai profil untuk di-merge ke Request sebelum validasi.
     *
     * @param  array<string, string>  $peta  nama kolom form => nama field profil
     * @return array<string, string|int>
     */
    public function untukRequest(array $peta): array
    {
        $hasil = [];

        foreach ($peta as $kolom => $field) {
            if ($this->terisi($field)) {
                $hasil[$kolom] = $this->nilai($field);
            }
        }

        return $hasil;
    }

    /**
     * Daftar field profil yang masih kosong — dipakai untuk mengingatkan pemohon
     * agar melengkapi profilnya sekali saja, bukan mengetik ulang tiap permohonan.
     *
     * @param  array<string>  $fields
     * @return array<string>  label siap tampil
     */
    public function yangBelumTerisi(array $fields): array
    {
        $label = [
            'nama' => 'Nama',
            'nip' => 'NIP',
            'jabatan' => 'Jabatan',
            'telepon' => 'Nomor telepon',
            'email' => 'Alamat surel',
            'unit_kerja_id' => 'Perangkat daerah',
        ];

        return collect($fields)
            ->reject(fn (string $field) => $this->terisi($field))
            ->map(fn (string $field) => $label[$field] ?? $field)
            ->values()
            ->all();
    }
}
