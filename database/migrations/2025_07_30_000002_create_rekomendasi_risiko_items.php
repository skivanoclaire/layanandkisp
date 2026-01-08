<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rekomendasi_risiko_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekomendasi_aplikasi_form_id')->constrained()->onDelete('cascade');

            $table->string('jenis_risiko');
            $table->text('uraian_risiko')->nullable();
            $table->string('penyebab')->nullable();
            $table->string('dampak')->nullable();
            $table->string('level_kemungkinan')->nullable();
            $table->string('level_dampak')->nullable();
            $table->string('besaran_risiko')->nullable();
            $table->boolean('perlu_penanganan')->default(true);

            // Evaluasi & Rencana
            $table->text('opsi_penanganan')->nullable();
            $table->text('rencana_aksi')->nullable();
            $table->string('jadwal_implementasi')->nullable();
            $table->string('penanggung_jawab')->nullable();
            $table->boolean('risiko_residual')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_risiko_items');
    }
};
