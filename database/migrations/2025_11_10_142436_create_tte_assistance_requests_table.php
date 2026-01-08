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
        Schema::create('tte_assistance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Data Pemohon (auto-filled from user)
            $table->string('nama');
            $table->string('nip');
            $table->string('email_resmi'); // Email kaltaraprov
            $table->string('instansi');
            $table->string('jabatan');
            $table->string('no_hp');

            // Waktu Permohonan Pendampingan
            $table->dateTime('waktu_pendampingan');

            // Upload Surat
            $table->string('surat_permohonan_path')->nullable();

            // Status & Admin Notes
            $table->enum('status', ['menunggu', 'proses', 'selesai', 'ditolak'])->default('menunggu');
            $table->text('admin_notes')->nullable();
            $table->text('keterangan_admin')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tte_assistance_requests');
    }
};
