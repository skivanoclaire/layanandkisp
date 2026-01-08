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
        Schema::create('tte_certificate_update_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Data pemohon (auto-filled)
            $table->string('nama');
            $table->string('nip', 18);
            $table->string('email_resmi');
            $table->string('no_hp', 20);

            // Data tambahan dari user
            $table->string('instansi')->nullable();
            $table->string('jabatan')->nullable();

            // Data sertifikat yang akan diperbaharui
            $table->string('nomor_sertifikat_lama')->nullable();
            $table->date('tgl_kadaluarsa')->nullable();

            // Status dan catatan
            $table->enum('status', ['menunggu', 'diproses', 'selesai', 'ditolak'])->default('menunggu');
            $table->text('admin_notes')->nullable();
            $table->text('keterangan_admin')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');

            // Timestamps tracking
            $table->timestamp('submitted_at')->nullable();
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
        Schema::dropIfExists('tte_certificate_update_requests');
    }
};
