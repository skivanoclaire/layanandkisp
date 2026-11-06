<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permohonan pasang baru layanan internet (kantor / ruangan).
     */
    public function up(): void
    {
        Schema::create('internet_installation_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no')->unique();

            // Data pemohon
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama');
            $table->string('nip');
            $table->string('no_hp', 20);
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();

            // Lokasi & kebutuhan pemasangan
            $table->text('lokasi_pemasangan');
            $table->string('ruangan')->nullable();
            $table->string('pic_lokasi')->nullable();
            $table->unsignedTinyInteger('jumlah_titik')->default(1);
            $table->text('uraian_kebutuhan');

            // Status: menunggu, proses, selesai, ditolak
            $table->enum('status', ['menunggu', 'proses', 'selesai', 'ditolak'])->default('menunggu');

            // Tanggapan admin
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internet_installation_requests');
    }
};
