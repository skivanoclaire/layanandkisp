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
            // Change all text fields to LONGTEXT to support CKEditor HTML content
            $table->longText('dasar_hukum')->nullable()->change();
            $table->longText('permasalahan_kebutuhan')->nullable()->change();
            $table->longText('maksud_tujuan')->nullable()->change();
            $table->longText('ruang_lingkup')->nullable()->change();
            $table->longText('analisis_biaya_manfaat')->nullable()->change();
            $table->longText('analisis_risiko')->nullable()->change();
            $table->longText('stakeholder_eksternal')->nullable()->change();
            $table->longText('perencanaan_ruang_lingkup')->nullable()->change();
            $table->longText('perencanaan_proses_bisnis')->nullable()->change();
            $table->longText('kerangka_kerja')->nullable()->change();
            $table->longText('pelaksana_pembangunan')->nullable()->change();
            $table->longText('peran_tanggung_jawab')->nullable()->change();
            $table->longText('jadwal_pelaksanaan')->nullable()->change();
            $table->longText('rencana_aksi')->nullable()->change();
            $table->longText('keamanan_informasi')->nullable()->change();
            $table->longText('sumber_daya')->nullable()->change();
            $table->longText('indikator_keberhasilan')->nullable()->change();
            $table->longText('alih_pengetahuan')->nullable()->change();
            $table->longText('pemantauan_pelaporan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            // Revert back to TEXT (note: this may truncate data)
            $table->text('dasar_hukum')->nullable()->change();
            $table->text('permasalahan_kebutuhan')->nullable()->change();
            $table->text('maksud_tujuan')->nullable()->change();
            $table->text('ruang_lingkup')->nullable()->change();
            $table->text('analisis_biaya_manfaat')->nullable()->change();
            $table->text('analisis_risiko')->nullable()->change();
            $table->text('stakeholder_eksternal')->nullable()->change();
            $table->text('perencanaan_ruang_lingkup')->nullable()->change();
            $table->text('perencanaan_proses_bisnis')->nullable()->change();
            $table->text('kerangka_kerja')->nullable()->change();
            $table->text('pelaksana_pembangunan')->nullable()->change();
            $table->text('peran_tanggung_jawab')->nullable()->change();
            $table->text('jadwal_pelaksanaan')->nullable()->change();
            $table->text('rencana_aksi')->nullable()->change();
            $table->text('keamanan_informasi')->nullable()->change();
            $table->text('sumber_daya')->nullable()->change();
            $table->text('indikator_keberhasilan')->nullable()->change();
            $table->text('alih_pengetahuan')->nullable()->change();
            $table->text('pemantauan_pelaporan')->nullable()->change();
        });
    }
};
