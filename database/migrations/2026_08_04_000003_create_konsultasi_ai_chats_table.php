<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat percakapan "Tanya Langsung".
     */
    public function up(): void
    {
        Schema::create('konsultasi_ai_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('pertanyaan');
            $table->longText('jawaban')->nullable();
            // ai = dijawab Claude, faq = dijawab knowledge base contoh, fallback = tidak ditemukan
            $table->string('sumber', 20)->default('fallback');
            $table->string('model', 60)->nullable();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konsultasi_ai_chats');
    }
};
