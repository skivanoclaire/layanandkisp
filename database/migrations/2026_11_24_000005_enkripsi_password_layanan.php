<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * Audit A-15 & A-25: kata sandi VPN hasil reset dan meeting password vidcon disimpan plaintext.
 * Migration ini mengenkripsi nilai lama yang belum terenkripsi. Nilai yang sudah terenkripsi dilewati,
 * sehingga aman dijalankan ulang.
 */
return new class extends Migration
{
    /** @var array<int, array{0: string, 1: string}> */
    private array $kolom = [
        ['vpn_resets', 'password_vpn_baru'],
        ['vidcon_requests', 'meeting_password'],
        ['vidcon_requests', 'pemohon_meeting_password'],
        ['vidcon_data', 'meeting_password'],
    ];

    public function up(): void
    {
        foreach ($this->kolom as [$tabel, $kolom]) {
            DB::table($tabel)->whereNotNull($kolom)->where($kolom, '!=', '')->orderBy('id')
                ->chunkById(200, function ($baris) use ($tabel, $kolom) {
                    foreach ($baris as $row) {
                        if ($this->sudahTerenkripsi($row->{$kolom})) {
                            continue;
                        }

                        DB::table($tabel)->where('id', $row->id)
                            ->update([$kolom => Crypt::encryptString($row->{$kolom})]);
                    }
                });
        }
    }

    public function down(): void
    {
        // Tidak dibalik: mengembalikan plaintext akan membuka kembali celah A-15.
    }

    private function sudahTerenkripsi(string $nilai): bool
    {
        try {
            Crypt::decryptString($nilai);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }
};
