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
        Schema::create('rekomendasi_pengiriman_surat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekomendasi_surat_id')
                ->constrained('rekomendasi_surat')
                ->onDelete('cascade');
            $table->enum('metode_pengiriman', ['pos', 'email', 'online', 'kurir']);
            $table->date('tanggal_pengiriman');
            $table->string('nomor_resi')->nullable();
            $table->string('email_tujuan')->nullable();
            $table->string('file_bukti_pengiriman')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('rekomendasi_surat_id');
            $table->index('tanggal_pengiriman');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_pengiriman_surat');
    }
};
