<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * NIP dan NIK (hash) harus unik supaya satu identitas ASN tidak bisa dipakai banyak akun.
 *
 * Migration ini berhenti (tidak mengubah apa pun) bila masih ada NIP/NIK ganda.
 * Jalankan `php artisan users:audit-identitas` untuk melihat daftarnya, bereskan dulu,
 * lalu jalankan migrate lagi.
 */
return new class extends Migration
{
    public function up(): void
    {
        // String kosong dianggap "tidak ada" agar tidak bentrok di index unik.
        DB::table('users')->where('nip', '')->update(['nip' => null]);
        DB::table('users')->where('nik_hash', '')->update(['nik_hash' => null]);

        $gandaNip = DB::table('users')->whereNotNull('nip')->select('nip')
            ->groupBy('nip')->havingRaw('COUNT(*) > 1')->count();
        $gandaNik = DB::table('users')->whereNotNull('nik_hash')->select('nik_hash')
            ->groupBy('nik_hash')->havingRaw('COUNT(*) > 1')->count();

        if ($gandaNip > 0 || $gandaNik > 0) {
            throw new RuntimeException(
                "Masih ada NIP ganda ({$gandaNip}) atau NIK ganda ({$gandaNik}). "
                . 'Jalankan "php artisan users:audit-identitas", bereskan datanya, lalu migrate ulang.'
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('nip', 'users_nip_unique');
            $table->unique('nik_hash', 'users_nik_hash_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_nip_unique');
            $table->dropUnique('users_nik_hash_unique');
        });
    }
};
