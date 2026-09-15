<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Form 2.3 - Pemilihan variabel data. Setiap variabel yang dipilih wajib
     * disertai kolom kegunaan/alasan kebutuhan (bahan penilaian verifikasi substansi).
     */
    public function up(): void
    {
        Schema::create('dtsen_request_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dtsen_data_request_id')->constrained('dtsen_data_requests')->cascadeOnDelete();
            $table->foreignId('dtsen_variable_id')->constrained('dtsen_variables')->cascadeOnDelete();
            $table->text('kegunaan');
            // Diisi verifikator substansi bila sebagian variabel tidak disetujui.
            $table->boolean('disetujui')->default(true);
            $table->text('catatan_verifikator')->nullable();
            $table->timestamps();

            $table->unique(['dtsen_data_request_id', 'dtsen_variable_id'], 'dtsen_req_var_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_request_variables');
    }
};
