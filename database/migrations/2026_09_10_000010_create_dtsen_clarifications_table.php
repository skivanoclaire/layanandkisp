<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Form 3.3 - Berita Acara Klarifikasi (opsional, bila pemohon diundang klarifikasi
     * oleh Koordinator Forum Satu Data Daerah).
     */
    public function up(): void
    {
        Schema::create('dtsen_clarifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dtsen_data_request_id')->constrained('dtsen_data_requests')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('tempat_media', 255);
            $table->text('peserta');
            $table->text('pokok_klarifikasi');
            $table->text('hasil')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_clarifications');
    }
};
