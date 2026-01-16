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
        Schema::create('rekomendasi_verifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekomendasi_aplikasi_form_id')
                ->constrained('rekomendasi_aplikasi_forms')
                ->onDelete('cascade');
            $table->foreignId('verifikator_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->enum('status', [
                'menunggu',
                'sedang_diverifikasi',
                'disetujui',
                'ditolak',
                'perlu_revisi'
            ])->default('menunggu');
            $table->boolean('checklist_analisis_kebutuhan')->default(false);
            $table->boolean('checklist_perencanaan')->default(false);
            $table->boolean('checklist_manajemen_risiko')->default(false);
            $table->boolean('checklist_anggaran')->default(false);
            $table->boolean('checklist_timeline')->default(false);
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('rekomendasi_aplikasi_form_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_verifikasi');
    }
};
