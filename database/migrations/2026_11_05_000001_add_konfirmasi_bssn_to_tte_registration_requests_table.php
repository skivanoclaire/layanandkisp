<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tte_registration_requests', function (Blueprint $table) {
            // Waktu pemohon menyatakan sudah melakukan registrasi di BSSN (via tautan WA).
            $table->timestamp('konfirmasi_bssn_at')->nullable()->after('keterangan_admin');
        });
    }

    public function down(): void
    {
        Schema::table('tte_registration_requests', function (Blueprint $table) {
            $table->dropColumn('konfirmasi_bssn_at');
        });
    }
};
