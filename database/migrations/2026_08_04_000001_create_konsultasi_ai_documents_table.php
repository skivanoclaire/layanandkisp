<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dokumen dasar knowledge base Konsultasi SPBE Berbasis AI.
     * Diunggah admin, isinya dipakai sebagai konteks jawaban AI.
     */
    public function up(): void
    {
        Schema::create('konsultasi_ai_documents', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kategori')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_mime')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            // Teks hasil ekstraksi (atau diketik manual) yang dikirim sebagai konteks ke AI.
            $table->longText('konten')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konsultasi_ai_documents');
    }
};
