<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Akun Zoom (001-004) dipilih admin langsung dari halaman permohonan vidcon,
     * lalu ikut tersalin ke Data Fasilitasi Vidcon saat permohonan disetujui —
     * sehingga admin tidak perlu mengedit data vidcon secara manual.
     */
    public function up(): void
    {
        Schema::table('vidcon_requests', function (Blueprint $table) {
            $table->string('akun_zoom', 10)->nullable()->after('meeting_password');
        });
    }

    public function down(): void
    {
        Schema::table('vidcon_requests', function (Blueprint $table) {
            $table->dropColumn('akun_zoom');
        });
    }
};
