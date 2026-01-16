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
        Schema::create('rekomendasi_evaluasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekomendasi_aplikasi_form_id')
                ->constrained('rekomendasi_aplikasi_forms')
                ->onDelete('cascade');
            $table->string('periode'); // 'Semester 1 2025', 'Tahun 2025', etc.
            $table->date('tanggal_evaluasi');
            $table->text('kebijakan_internal')->nullable();

            // Ratings (1-5)
            $table->integer('rating_fungsionalitas')->nullable();
            $table->integer('rating_keamanan')->nullable();
            $table->integer('rating_performance')->nullable();
            $table->integer('rating_ux')->nullable();

            // Usage statistics
            $table->integer('jumlah_pengguna')->nullable();
            $table->string('frekuensi_akses')->nullable();
            $table->text('fitur_populer')->nullable();

            // Files and feedback
            $table->string('file_survey')->nullable();
            $table->text('feedback_pengguna')->nullable();
            $table->string('file_laporan_evaluasi')->nullable();

            // Recommendations
            $table->enum('rekomendasi_tindak_lanjut', [
                'tetap_digunakan',
                'perlu_pengembangan',
                'perlu_perbaikan',
                'penghentian'
            ])->nullable();

            $table->string('file_laporan_pimpinan')->nullable();
            $table->date('tanggal_penyampaian_pimpinan')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('rekomendasi_aplikasi_form_id');
            $table->index('tanggal_evaluasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_evaluasi');
    }
};
