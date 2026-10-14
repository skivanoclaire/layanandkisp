<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul Tata Kelola Keamanan Informasi: integrasi COBIT 2019 dan ISO/IEC 27001:2022.
     * Tahapan: design factors -> prioritas objektif -> pemetaan -> validasi pakar (Delphi) ->
     * asesmen kapabilitas dan status kontrol -> kesenjangan dan rekomendasi.
     * Responden dan pakar mengisi melalui akun portal masing-masing.
     * Status disimpan sebagai string (bukan ENUM) dan divalidasi di aplikasi.
     */
    public function up(): void
    {
        Schema::create('tata_kelola_keamanan_evaluasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('instansi')->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('is_simulasi')->default(false);
            $table->timestamps();
        });

        // Responden internal (pimpinan/informan RACI) yang mengisi lewat akun portal masing-masing.
        Schema::create('tata_kelola_keamanan_respondens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_id')->constrained('tata_kelola_keamanan_evaluasis')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('jabatan');
            $table->string('peran_raci')->nullable();
            $table->boolean('isi_df')->default(false);
            // Kode objektif yang ditugaskan untuk kuesioner aktivitas
            $table->json('objektif')->nullable();
            $table->timestamps();
            $table->unique(['evaluasi_id', 'user_id'], 'tkk_responden_unik');
        });

        // Satu baris per design factor per responden; responden kosong = nilai akhir hasil FGD.
        // responden_id terisi bila jawaban diisi sendiri oleh responden melalui akunnya.
        Schema::create('tata_kelola_keamanan_design_factors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_id')->constrained('tata_kelola_keamanan_evaluasis')->cascadeOnDelete();
            $table->foreignId('responden_id')->nullable()->constrained('tata_kelola_keamanan_respondens')->nullOnDelete();
            $table->string('df_kode', 5);
            $table->string('responden', 100)->default('');
            $table->json('nilai')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['evaluasi_id', 'df_kode', 'responden'], 'tkk_df_unik');
        });

        Schema::create('tata_kelola_keamanan_objektifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_id')->constrained('tata_kelola_keamanan_evaluasis')->cascadeOnDelete();
            $table->string('kode', 5);
            // Skor kepentingan relatif dari COBIT 2019 Design Toolkit (-100..100)
            $table->smallInteger('skor_kepentingan')->nullable();
            $table->boolean('terkait_keamanan')->default(false);
            $table->boolean('dipilih')->default(false);
            // Bila kosong, target dihitung dari skor (Tabel 3.2)
            $table->unsignedTinyInteger('target_manual')->nullable();
            $table->text('alasan')->nullable();
            $table->timestamps();
            $table->unique(['evaluasi_id', 'kode'], 'tkk_obj_unik');
        });

        Schema::create('tata_kelola_keamanan_praktiks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_id')->constrained('tata_kelola_keamanan_evaluasis')->cascadeOnDelete();
            $table->string('objektif_kode', 5);
            $table->string('kode', 10);
            $table->string('nama');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
            $table->unique(['evaluasi_id', 'kode'], 'tkk_praktik_unik');
        });

        Schema::create('tata_kelola_keamanan_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('praktik_id')->constrained('tata_kelola_keamanan_praktiks')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->text('uraian');
            $table->text('bukti_diharapkan')->nullable();
            // null = belum dinilai, true = terpenuhi, false = belum terpenuhi
            $table->boolean('terpenuhi')->nullable();
            $table->text('bukti_ditemukan')->nullable();
            $table->json('sumber_bukti')->nullable();
            $table->string('informan')->nullable();
            $table->string('lampiran_path')->nullable();
            $table->timestamp('dinilai_at')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        // Jawaban informan atas aktivitas (bahan wawancara dan validasi bukti, bukan hasil akhir).
        Schema::create('tata_kelola_keamanan_jawaban_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aktivitas_id')->constrained('tata_kelola_keamanan_aktivitas')->cascadeOnDelete();
            $table->foreignId('responden_id')->constrained('tata_kelola_keamanan_respondens')->cascadeOnDelete();
            // ya | tidak | tidak_tahu
            $table->string('jawaban', 20);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['aktivitas_id', 'responden_id'], 'tkk_jawaban_unik');
        });

        Schema::create('tata_kelola_keamanan_pemetaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_id')->constrained('tata_kelola_keamanan_evaluasis')->cascadeOnDelete();
            $table->foreignId('praktik_id')->constrained('tata_kelola_keamanan_praktiks')->cascadeOnDelete();
            $table->json('iso_items')->nullable();
            $table->json('regulasi_items')->nullable();
            $table->text('bukti_diharapkan')->nullable();
            // draf | disepakati | dikeluarkan
            $table->string('status', 20)->default('draf');
            $table->unsignedTinyInteger('putaran_disepakati')->nullable();
            $table->text('catatan_revisi')->nullable();
            $table->timestamps();
        });

        Schema::create('tata_kelola_keamanan_pakars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_id')->constrained('tata_kelola_keamanan_evaluasis')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('instansi')->nullable();
            $table->string('keahlian')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->unique(['evaluasi_id', 'user_id'], 'tkk_pakar_unik');
        });

        Schema::create('tata_kelola_keamanan_putarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_id')->constrained('tata_kelola_keamanan_evaluasis')->cascadeOnDelete();
            $table->unsignedTinyInteger('nomor');
            // dibuka | ditutup
            $table->string('status', 20)->default('dibuka');
            $table->timestamp('dibuka_at')->nullable();
            $table->timestamp('ditutup_at')->nullable();
            $table->timestamps();
            $table->unique(['evaluasi_id', 'nomor'], 'tkk_putaran_unik');
        });

        Schema::create('tata_kelola_keamanan_penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('putaran_id')->constrained('tata_kelola_keamanan_putarans')->cascadeOnDelete();
            $table->foreignId('pakar_id')->constrained('tata_kelola_keamanan_pakars')->cascadeOnDelete();
            $table->foreignId('pemetaan_id')->constrained('tata_kelola_keamanan_pemetaans')->cascadeOnDelete();
            $table->boolean('setuju');
            $table->text('saran')->nullable();
            $table->timestamps();
            $table->unique(['putaran_id', 'pakar_id', 'pemetaan_id'], 'tkk_penilaian_unik');
        });

        Schema::create('tata_kelola_keamanan_kontrols', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_id')->constrained('tata_kelola_keamanan_evaluasis')->cascadeOnDelete();
            $table->string('iso_kode', 10);
            // diterapkan | sebagian | belum | tidak_berlaku
            $table->string('status', 20)->nullable();
            $table->text('bukti')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['evaluasi_id', 'iso_kode'], 'tkk_kontrol_unik');
        });

        Schema::create('tata_kelola_keamanan_rekomendasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_id')->constrained('tata_kelola_keamanan_evaluasis')->cascadeOnDelete();
            $table->string('objektif_kode', 5)->nullable();
            $table->foreignId('praktik_id')->nullable()->constrained('tata_kelola_keamanan_praktiks')->nullOnDelete();
            $table->string('iso_kode', 10)->nullable();
            $table->text('uraian');
            // pendek | menengah | panjang
            $table->string('horizon', 20)->default('pendek');
            $table->unsignedSmallInteger('prioritas')->default(1);
            $table->string('penanggung_jawab')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tata_kelola_keamanan_rekomendasis');
        Schema::dropIfExists('tata_kelola_keamanan_kontrols');
        Schema::dropIfExists('tata_kelola_keamanan_penilaians');
        Schema::dropIfExists('tata_kelola_keamanan_putarans');
        Schema::dropIfExists('tata_kelola_keamanan_pakars');
        Schema::dropIfExists('tata_kelola_keamanan_pemetaans');
        Schema::dropIfExists('tata_kelola_keamanan_jawaban_aktivitas');
        Schema::dropIfExists('tata_kelola_keamanan_aktivitas');
        Schema::dropIfExists('tata_kelola_keamanan_praktiks');
        Schema::dropIfExists('tata_kelola_keamanan_objektifs');
        Schema::dropIfExists('tata_kelola_keamanan_design_factors');
        Schema::dropIfExists('tata_kelola_keamanan_respondens');
        Schema::dropIfExists('tata_kelola_keamanan_evaluasis');
    }
};
