<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Berkas bukti yang diunggah informan saat menjawab kuesioner aktivitas.
     * Berkas disimpan di disk privat; hanya pengunggah dan pengelola yang dapat mengunduh.
     */
    public function up(): void
    {
        Schema::create('tata_kelola_keamanan_lampiran_jawabans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jawaban_id')->constrained('tata_kelola_keamanan_jawaban_aktivitas')->cascadeOnDelete();
            $table->string('path');
            $table->string('nama_asli');
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('ukuran')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tata_kelola_keamanan_lampiran_jawabans');
    }
};
