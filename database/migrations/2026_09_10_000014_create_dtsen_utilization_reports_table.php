<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Form 5.1 - Laporan pemanfaatan DTSEN (periodik minimal 1x/6 bulan).
     * Direkap berjenjang oleh Bapperida (koordinator Forum SDD) & DKISP (prosesor).
     */
    public function up(): void
    {
        Schema::create('dtsen_utilization_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dtsen_data_request_id')->constrained('dtsen_data_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('periode_mulai');
            $table->date('periode_akhir');
            $table->string('nama_program', 255);
            // Subset id dtsen_variables yang benar-benar dimanfaatkan.
            $table->json('variabel_ids')->nullable();
            $table->text('hasil_pemanfaatan');
            $table->text('kendala')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->enum('status', ['terkirim', 'diverifikasi', 'perlu_perbaikan'])->default('terkirim')->index();
            $table->text('catatan_review')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_utilization_reports');
    }
};
