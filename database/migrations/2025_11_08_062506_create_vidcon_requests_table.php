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
        Schema::create('vidcon_requests', function (Blueprint $table) {
            $table->id();

            // Ticket and User Information
            $table->string('ticket_no')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama');
            $table->string('nip')->nullable();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->onDelete('set null');
            $table->string('email_pemohon');
            $table->string('no_hp')->nullable();

            // Video Conference Details
            $table->string('judul_kegiatan');
            $table->text('deskripsi_kegiatan')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->enum('platform', ['Zoom', 'Google Meet', 'Microsoft Teams', 'YouTube Live', 'Lainnya'])->default('Zoom');
            $table->string('platform_lainnya')->nullable();
            $table->integer('jumlah_peserta')->nullable();
            $table->text('keperluan_khusus')->nullable();

            // Request Workflow
            $table->enum('status', ['menunggu', 'proses', 'selesai', 'ditolak'])->default('menunggu');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // Admin Response
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_notes')->nullable();

            // Approval Result (when status = selesai)
            $table->text('link_meeting')->nullable();
            $table->text('meeting_id')->nullable();
            $table->text('meeting_password')->nullable();
            $table->text('informasi_tambahan')->nullable();
            $table->foreignId('operator_assigned')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('user_id');
            $table->index('tanggal_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vidcon_requests');
    }
};
