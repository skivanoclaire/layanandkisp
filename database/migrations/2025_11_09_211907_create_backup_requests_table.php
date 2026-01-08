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
        Schema::create('backup_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no')->unique();

            // User info
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama');
            $table->string('nip');
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->onDelete('set null');

            // Backup types (checkbox - can be multiple)
            $table->boolean('backup_virtual_machine')->default(false);
            $table->boolean('backup_aplikasi')->default(false);
            $table->boolean('backup_database')->default(false);

            // Backup schedule and retention
            $table->string('jadwal_backup'); // e.g., "Harian", "Mingguan", "Bulanan"
            $table->integer('retensi_hari'); // Retention in days

            // Additional info
            $table->text('keterangan')->nullable();

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
        Schema::dropIfExists('backup_requests');
    }
};
