<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master unit organisasi (Bidang / Sekretariat) yang berada di bawah sebuah Unit Kerja (Perangkat Daerah).
     * Dipakai sebagai tujuan permohonan email unit organisasi.
     */
    public function up(): void
    {
        Schema::create('unit_organisasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 255);
            $table->string('tipe', 30)->default('Bidang'); // Bidang | Sekretariat | Lainnya
            $table->foreignId('unit_kerja_id')->constrained('unit_kerjas')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_organisasis');
    }
};
