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
        Schema::create('vidcon_data', function (Blueprint $table) {
            $table->id();
            $table->integer('no')->nullable();
            $table->string('nama_instansi')->nullable();
            $table->string('nomor_surat')->nullable();
            $table->text('judul_kegiatan')->nullable();
            $table->string('lokasi')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('platform')->nullable();
            $table->string('operator')->nullable();
            $table->string('dokumentasi')->nullable();
            $table->string('akun_zoom')->nullable();
            $table->text('informasi_pimpinan')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vidcon_data');
    }
};
