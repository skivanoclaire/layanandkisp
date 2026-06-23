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
        Schema::table('vidcon_requests', function (Blueprint $table) {
            // Lokasi tempat kegiatan dilaksanakan
            $table->string('lokasi_kegiatan')->nullable()->after('deskripsi_kegiatan');

            // Jenis layanan yang diminta:
            // - link_host          : Link Host saja
            // - link_host_operator : Link Host + Operator
            // - operator           : Operator saja
            $table->enum('jenis_layanan', ['link_host', 'link_host_operator', 'operator'])
                ->default('link_host')
                ->after('platform_lainnya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vidcon_requests', function (Blueprint $table) {
            $table->dropColumn(['lokasi_kegiatan', 'jenis_layanan']);
        });
    }
};
