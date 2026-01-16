<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan field sesuai Permenkomdigi No. 6 Tahun 2025
     * tentang Pembangunan Aplikasi Khusus
     */
    public function up(): void
    {
        Schema::table('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            // === ANALISIS KEBUTUHAN ===
            // Dasar hukum yang mendasari kebutuhan aplikasi
            $table->longText('dasar_hukum')->nullable()->after('prioritas');

            // Uraian permasalahan/kebutuhan yang dihadapi
            $table->longText('uraian_permasalahan')->nullable()->after('dasar_hukum');

            // Pihak-pihak terkait (stakeholder)
            $table->longText('pihak_terkait')->nullable()->after('uraian_permasalahan');

            // Ruang lingkup pengembangan
            $table->longText('ruang_lingkup')->nullable()->after('pihak_terkait');

            // Analisis biaya dan manfaat
            $table->longText('analisis_biaya_manfaat')->nullable()->after('ruang_lingkup');

            // Lokasi implementasi aplikasi
            $table->string('lokasi_implementasi')->nullable()->after('analisis_biaya_manfaat');

            // === PERENCANAAN ===
            // Uraian ruang lingkup perencanaan
            $table->longText('uraian_ruang_lingkup')->nullable()->after('lokasi_implementasi');

            // Proses bisnis yang akan didukung
            $table->longText('proses_bisnis')->nullable()->after('uraian_ruang_lingkup');

            // Kerangka kerja/metodologi pengembangan
            $table->longText('kerangka_kerja')->nullable()->after('proses_bisnis');

            // Pelaksana pembangunan (Swakelola atau Pihak Ketiga)
            $table->enum('pelaksana_pembangunan', ['swakelola', 'pihak_ketiga'])->nullable()->after('kerangka_kerja');

            // Peran dan tanggung jawab tim
            $table->longText('peran_tanggung_jawab')->nullable()->after('pelaksana_pembangunan');

            // Jadwal pelaksanaan pengembangan
            $table->longText('jadwal_pelaksanaan')->nullable()->after('peran_tanggung_jawab');

            // Rencana aksi yang akan dilakukan
            $table->longText('rencana_aksi')->nullable()->after('jadwal_pelaksanaan');

            // Aspek keamanan informasi
            $table->longText('keamanan_informasi')->nullable()->after('rencana_aksi');

            // Sumber daya manusia yang dibutuhkan
            $table->longText('sumber_daya_manusia')->nullable()->after('keamanan_informasi');

            // Sumber daya anggaran
            $table->longText('sumber_daya_anggaran')->nullable()->after('sumber_daya_manusia');

            // Sumber daya sarana prasarana
            $table->longText('sumber_daya_sarana')->nullable()->after('sumber_daya_anggaran');

            // Indikator keberhasilan proyek
            $table->longText('indikator_keberhasilan')->nullable()->after('sumber_daya_sarana');

            // Rencana alih pengetahuan
            $table->longText('alih_pengetahuan')->nullable()->after('indikator_keberhasilan');

            // Rencana pemantauan dan pelaporan
            $table->longText('pemantauan_pelaporan')->nullable()->after('alih_pengetahuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            $table->dropColumn([
                // Analisis Kebutuhan
                'dasar_hukum',
                'uraian_permasalahan',
                'pihak_terkait',
                'ruang_lingkup',
                'analisis_biaya_manfaat',
                'lokasi_implementasi',
                // Perencanaan
                'uraian_ruang_lingkup',
                'proses_bisnis',
                'kerangka_kerja',
                'pelaksana_pembangunan',
                'peran_tanggung_jawab',
                'jadwal_pelaksanaan',
                'rencana_aksi',
                'keamanan_informasi',
                'sumber_daya_manusia',
                'sumber_daya_anggaran',
                'sumber_daya_sarana',
                'indikator_keberhasilan',
                'alih_pengetahuan',
                'pemantauan_pelaporan',
            ]);
        });
    }
};
