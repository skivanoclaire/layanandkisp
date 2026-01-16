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
            // Remove V1 columns that are no longer used
            $table->dropColumn([
                'judul_aplikasi',
                'dasar_hukum',
                'permasalahan_kebutuhan',
                'pihak_terkait',
                'stakeholder_internal',
                'stakeholder_eksternal',
                'maksud_tujuan',
                'ruang_lingkup',
                'analisis_biaya_manfaat',
                'analisis_risiko',
                'target_waktu',
                'sasaran_pengguna',
                'lokasi_implementasi',
                'perencanaan_ruang_lingkup',
                'perencanaan_proses_bisnis',
                'kerangka_kerja',
                'pelaksana_pembangunan',
                'peran_tanggung_jawab',
                'jadwal_pelaksanaan',
                'rencana_aksi',
                'keamanan_informasi',
                'sumber_daya',
                'indikator_keberhasilan',
                'alih_pengetahuan',
                'pemantauan_pelaporan',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            // Re-add V1 columns if rollback needed
            $table->string('judul_aplikasi')->nullable();
            $table->longText('dasar_hukum')->nullable();
            $table->longText('permasalahan_kebutuhan')->nullable();
            $table->longText('pihak_terkait')->nullable();
            $table->json('stakeholder_internal')->nullable();
            $table->longText('stakeholder_eksternal')->nullable();
            $table->longText('maksud_tujuan')->nullable();
            $table->longText('ruang_lingkup')->nullable();
            $table->longText('analisis_biaya_manfaat')->nullable();
            $table->longText('analisis_risiko')->nullable();
            $table->longText('target_waktu')->nullable();
            $table->longText('sasaran_pengguna')->nullable();
            $table->longText('lokasi_implementasi')->nullable();
            $table->longText('perencanaan_ruang_lingkup')->nullable();
            $table->longText('perencanaan_proses_bisnis')->nullable();
            $table->longText('kerangka_kerja')->nullable();
            $table->longText('pelaksana_pembangunan')->nullable();
            $table->longText('peran_tanggung_jawab')->nullable();
            $table->longText('jadwal_pelaksanaan')->nullable();
            $table->longText('rencana_aksi')->nullable();
            $table->longText('keamanan_informasi')->nullable();
            $table->longText('sumber_daya')->nullable();
            $table->longText('indikator_keberhasilan')->nullable();
            $table->longText('alih_pengetahuan')->nullable();
            $table->longText('pemantauan_pelaporan')->nullable();
        });
    }
};
