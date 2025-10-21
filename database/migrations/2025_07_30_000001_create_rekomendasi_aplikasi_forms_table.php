<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // Contoh: TKT-REK-2025070001
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Dokumen Analisis Kebutuhan (Pasal 7)
            $table->string('judul_aplikasi');
            $table->text('dasar_hukum')->nullable();
            $table->text('permasalahan_kebutuhan')->nullable();
            $table->text('pihak_terkait')->nullable();
            $table->text('maksud_tujuan')->nullable();
            $table->text('ruang_lingkup')->nullable();
            $table->text('analisis_biaya_manfaat')->nullable();
            $table->text('analisis_risiko')->nullable();
            $table->string('target_waktu')->nullable();
            $table->string('sasaran_pengguna')->nullable();
            $table->string('lokasi_implementasi')->nullable();

            // Dokumen Perencanaan (Pasal 8)
            $table->text('perencanaan_ruang_lingkup')->nullable();
            $table->text('perencanaan_proses_bisnis')->nullable();
            $table->string('kerangka_kerja')->nullable(); // agile, waterfall, dsb.
            $table->string('pelaksana_pembangunan')->nullable(); // swakelola, pihak ketiga
            $table->text('peran_tanggung_jawab')->nullable();
            $table->string('jadwal_pelaksanaan')->nullable();
            $table->text('rencana_aksi')->nullable();
            $table->text('keamanan_informasi')->nullable();
            $table->text('sumber_daya')->nullable(); // SDM, anggaran, sarana
            $table->text('indikator_keberhasilan')->nullable();
            $table->text('alih_pengetahuan')->nullable();
            $table->text('pemantauan_pelaporan')->nullable();

            $table->enum('status', ['draft', 'diajukan', 'diproses', 'disetujui', 'ditolak'])->default('diajukan');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_aplikasi_forms');
    }
};
