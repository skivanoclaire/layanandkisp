<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Form 5.3 - Laporan insiden keamanan data (Bab VI huruf C).
     * Wajib dilaporkan maks. 3x24 jam hari kerja sejak diketahui; `batas_pelaporan`
     * dipakai untuk timer & eskalasi ke Petugas Pelindung DTSEN.
     */
    public function up(): void
    {
        Schema::create('dtsen_incident_reports', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no', 30)->unique()->index();
            $table->foreignId('dtsen_data_request_id')->nullable()
                ->constrained('dtsen_data_requests')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->enum('jenis_insiden', ['kebocoran', 'penyalahgunaan', 'lainnya'])->default('lainnya');
            $table->dateTime('waktu_diketahui');
            $table->dateTime('batas_pelaporan')->nullable();
            $table->text('kronologi');
            $table->text('dampak');
            $table->text('tindakan_awal');
            $table->string('file_path', 255)->nullable();
            $table->enum('status', ['dilaporkan', 'ditangani', 'selesai'])->default('dilaporkan')->index();
            $table->boolean('terlambat')->default(false);
            $table->timestamp('escalated_at')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_incident_reports');
    }
};
