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
        Schema::table('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            $table->enum('fase_saat_ini', [
                'usulan',
                'verifikasi',
                'penandatanganan',
                'menunggu_kementerian',
                'pengembangan',
                'selesai',
                'ditolak'
            ])->default('usulan')->after('status');

            $table->string('repository_url')->nullable()->after('fase_saat_ini');
            $table->string('url_aplikasi_staging')->nullable()->after('repository_url');
            $table->string('url_aplikasi_production')->nullable()->after('url_aplikasi_staging');
            $table->string('ip_address_server')->nullable()->after('url_aplikasi_production');
            $table->string('domain_aplikasi')->nullable()->after('ip_address_server');
            $table->text('spesifikasi_server')->nullable()->after('domain_aplikasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            $table->dropColumn([
                'fase_saat_ini',
                'repository_url',
                'url_aplikasi_staging',
                'url_aplikasi_production',
                'ip_address_server',
                'domain_aplikasi',
                'spesifikasi_server'
            ]);
        });
    }
};
