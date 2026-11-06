<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master ruang rapat dan permohonan peminjamannya.
     */
    public function up(): void
    {
        Schema::create('meeting_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('lokasi')->nullable();
            $table->unsignedSmallInteger('kapasitas');
            $table->text('fasilitas')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('meeting_room_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no')->unique();

            // Data pemohon
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('meeting_room_id')->constrained('meeting_rooms')->restrictOnDelete();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->string('nama');
            $table->string('nip');
            $table->string('no_hp', 20);

            // Detail peminjaman
            $table->text('keperluan');
            $table->unsignedSmallInteger('jumlah_peserta');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');

            // Status: menunggu, disetujui, ditolak, dibatalkan
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'dibatalkan'])->default('menunggu');

            // Tanggapan admin
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index(['meeting_room_id', 'tanggal', 'status'], 'mrb_room_tanggal_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_room_bookings');
        Schema::dropIfExists('meeting_rooms');
    }
};
