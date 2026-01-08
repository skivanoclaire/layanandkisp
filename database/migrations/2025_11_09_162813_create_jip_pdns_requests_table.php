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
        Schema::create('jip_pdns_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no')->unique();

            // User info
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama');
            $table->string('nip');

            // Kab/Kota or Provinsi
            $table->boolean('is_kabupaten_kota')->default(false);
            $table->enum('kabupaten_kota', ['Bulungan', 'Malinau', 'Tana Tidung', 'Tarakan', 'Nunukan'])->nullable();
            $table->string('unit_kerja_manual')->nullable(); // For kab/kota
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->onDelete('set null'); // For provinsi

            // Request details
            $table->text('uraian_permohonan');
            $table->text('keterangan')->nullable(); // Untuk Segment IPSec info

            // Admin response
            $table->text('keterangan_admin')->nullable();

            // Status: menunggu, proses, selesai, ditolak
            $table->enum('status', ['menunggu', 'proses', 'selesai', 'ditolak'])->default('menunggu');

            // Workflow tracking
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
        Schema::dropIfExists('jip_pdns_requests');
    }
};
