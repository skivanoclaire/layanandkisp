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
        Schema::create('laporan_gangguan', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no')->unique();

            // User info
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama');
            $table->string('nip');
            $table->string('no_hp', 20);
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->onDelete('set null');

            // Report details
            $table->text('uraian_permasalahan');
            $table->string('lokasi_koordinat')->nullable();
            $table->json('lampiran_foto')->nullable(); // Store array of photo paths

            // Status: menunggu, proses, selesai, ditolak
            $table->enum('status', ['menunggu', 'proses', 'selesai', 'ditolak'])->default('menunggu');

            // Admin response
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_gangguan');
    }
};
