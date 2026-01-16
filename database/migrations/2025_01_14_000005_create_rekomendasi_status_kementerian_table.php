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
        Schema::create('rekomendasi_status_kementerian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekomendasi_surat_id')
                ->constrained('rekomendasi_surat')
                ->onDelete('cascade');
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'revisi_diminta'])
                ->default('menunggu');
            $table->string('file_respons_path')->nullable();
            $table->string('nomor_surat_respons')->nullable();
            $table->date('tanggal_surat_respons')->nullable();
            $table->date('tanggal_diterima')->nullable();
            $table->text('alasan_ditolak')->nullable();
            $table->json('catatan_revisi')->nullable(); // Array of revision points
            $table->text('catatan_internal')->nullable();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index('rekomendasi_surat_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_status_kementerian');
    }
};
