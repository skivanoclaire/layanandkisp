<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daftar pertanyaan-jawaban contoh. Dipakai sebagai:
     * 1. Sumber jawaban otomatis saat API Anthropic belum aktif (mode prototipe).
     * 2. Konteks tambahan saat AI sudah aktif.
     */
    public function up(): void
    {
        Schema::create('konsultasi_ai_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('pertanyaan');
            $table->text('jawaban');
            $table->string('kategori')->nullable();
            // Kata kunci pemicu, dipisah koma. Dipakai pencocokan saat mode prototipe.
            $table->string('kata_kunci')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('hit_count')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konsultasi_ai_faqs');
    }
};
