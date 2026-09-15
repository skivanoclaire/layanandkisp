<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Form G.1 - Pengaduan, saran & masukan layanan DTSEN (Bab IX).
     * Identitas pelapor dijamin kerahasiaannya: bila `is_anonim`, identitas
     * hanya terlihat oleh Prosesor DTSEN dan tidak ditampilkan pada rekap.
     */
    public function up(): void
    {
        Schema::create('dtsen_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no', 30)->unique()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('kategori', ['pengaduan', 'saran', 'masukan'])->default('pengaduan')->index();
            $table->string('nama_pelapor', 150)->nullable();
            $table->string('kontak_pelapor', 200)->nullable();
            $table->boolean('is_anonim')->default(false);
            $table->text('uraian');
            $table->string('file_path', 255)->nullable();
            $table->enum('status', ['baru', 'diproses', 'selesai'])->default('baru')->index();
            $table->text('tindak_lanjut')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_complaints');
    }
};
