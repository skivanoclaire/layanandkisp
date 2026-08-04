<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengaturan key-value untuk Konsultasi SPBE Berbasis AI
     * (aktivasi AI, model, system prompt, pesan fallback).
     */
    public function up(): void
    {
        Schema::create('konsultasi_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konsultasi_ai_settings');
    }
};
