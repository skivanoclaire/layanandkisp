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
        Schema::create('rekomendasi_surat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekomendasi_aplikasi_form_id')
                ->constrained('rekomendasi_aplikasi_forms')
                ->onDelete('cascade');
            $table->string('nomor_surat_draft')->nullable();
            $table->string('nomor_surat_final')->nullable();
            $table->date('tanggal_surat');
            $table->string('kota')->default('Tanjung Selor');
            $table->json('referensi_hukum')->nullable(); // Array of selected legal references
            $table->longText('template_content')->nullable(); // Generated letter content
            $table->json('lampiran')->nullable(); // Array of attachments
            $table->json('tembusan')->nullable(); // Array of CC recipients
            $table->string('file_draft_path')->nullable();
            $table->string('file_signed_path')->nullable();
            $table->string('penandatangan')->nullable(); // e.g., "Gubernur Kalimantan Utara"
            $table->string('nip_penandatangan')->nullable();
            $table->date('tanggal_ditandatangani')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('rekomendasi_aplikasi_form_id');
            $table->index('nomor_surat_final');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_surat');
    }
};
