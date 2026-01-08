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
        Schema::create('cloud_storage_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no')->unique();

            // User info
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama');
            $table->string('nip');
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->onDelete('set null');

            // Cloud Storage specs
            $table->integer('kapasitas_gb'); // Capacity in GB
            $table->enum('tipe', ['Internal Cloud (Synology)', 'GoogleDrive']);

            // Additional info
            $table->text('keterangan')->nullable();

            // Admin response
            $table->string('akses_url')->nullable(); // Access URL/Link
            $table->string('username')->nullable(); // Username if applicable
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
        Schema::dropIfExists('cloud_storage_requests');
    }
};
