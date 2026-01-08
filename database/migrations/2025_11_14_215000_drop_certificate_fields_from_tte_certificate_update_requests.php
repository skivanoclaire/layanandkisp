<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tte_certificate_update_requests', function (Blueprint $table) {
            $table->dropColumn(['nomor_sertifikat_lama', 'tgl_kadaluarsa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tte_certificate_update_requests', function (Blueprint $table) {
            $table->string('nomor_sertifikat_lama')->nullable();
            $table->date('tgl_kadaluarsa')->nullable();
        });
    }
};
