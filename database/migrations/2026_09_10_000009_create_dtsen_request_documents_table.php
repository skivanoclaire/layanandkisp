<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Form 2.5 - Dokumen pendukung (wajib level 4/BNBA) dan lampiran perbaikan
     * yang diunggah ulang saat permohonan dikembalikan.
     */
    public function up(): void
    {
        Schema::create('dtsen_request_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dtsen_data_request_id')->constrained('dtsen_data_requests')->cascadeOnDelete();
            $table->enum('jenis', ['pendukung', 'perbaikan', 'lainnya'])->default('pendukung')->index();
            $table->string('nama_dokumen', 255);
            $table->text('keterangan')->nullable();
            $table->string('file_path', 255);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_request_documents');
    }
};
