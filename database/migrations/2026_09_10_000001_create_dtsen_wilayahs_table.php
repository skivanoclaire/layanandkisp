<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master wilayah berjenjang (provinsi → kabupaten/kota → kecamatan → desa/kelurahan)
     * untuk isian "Cakupan Wilayah" pada Formulir Permintaan Data DTSEN (Lampiran II).
     */
    public function up(): void
    {
        Schema::create('dtsen_wilayahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('dtsen_wilayahs')->cascadeOnDelete();
            $table->enum('tingkat', ['provinsi', 'kabupaten_kota', 'kecamatan', 'desa_kelurahan'])->index();
            $table->string('kode', 20)->nullable()->index();
            $table->string('nama', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['parent_id', 'tingkat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_wilayahs');
    }
};
