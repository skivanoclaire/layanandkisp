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
        Schema::create('visitations', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no')->unique();

            // User info
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama');
            $table->string('nip');
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->onDelete('set null');

            // Visit purpose
            $table->enum('tujuan_kunjungan', [
                'Kunjungan & Inspeksi Formal',
                'Penempatan Aset',
                'Pengambilan Aset'
            ]);

            // Asset details (if tujuan is related to aset)
            $table->string('nama_aset')->nullable();
            $table->string('nomor_aset')->nullable();
            $table->text('catatan_aset')->nullable();

            // Visit details
            $table->date('tanggal_kunjungan');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->text('keterangan')->nullable();

            // Admin response
            $table->text('keterangan_admin')->nullable();

            // Status: menunggu, disetujui, ditolak, selesai
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'selesai'])->default('menunggu');

            // Workflow tracking
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitations');
    }
};
