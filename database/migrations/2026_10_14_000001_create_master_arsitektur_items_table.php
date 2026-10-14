<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master Data Arsitektur: satu baris = satu entri pada salah satu sheet
     * (proses bisnis, layanan, data, aplikasi, infrastruktur, keamanan, peta rencana).
     * Kolom yang dipakai untuk filter dasbor disimpan sebagai kolom tabel,
     * sisanya pada kolom JSON "atribut" (lihat config/master_arsitektur.php).
     */
    public function up(): void
    {
        Schema::create('master_arsitektur_items', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 40);
            $table->string('kode', 150)->nullable();
            $table->string('nama', 1000);
            $table->string('status', 100)->nullable();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            // Nama perangkat daerah sebagaimana tertulis pada sumber (Excel)
            $table->string('unit_kerja_teks')->nullable();
            $table->string('ref_1')->nullable();
            $table->string('ref_2')->nullable();
            $table->string('ref_3')->nullable();
            $table->string('tahun', 20)->nullable();
            $table->json('atribut')->nullable();
            // Sidik baris untuk mencocokkan data lama saat impor ulang
            $table->char('kunci', 40);
            $table->timestamps();

            $table->index(['jenis', 'kunci']);
            $table->index(['jenis', 'unit_kerja_id']);
            $table->index(['jenis', 'ref_1']);
            $table->index(['jenis', 'ref_2']);
            $table->index(['jenis', 'ref_3']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_arsitektur_items');
    }
};
