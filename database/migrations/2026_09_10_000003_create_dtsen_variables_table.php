<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master daftar variabel/indikator DTSEN — dikelola Bapperida selaku koordinator
     * Forum Satu Data Daerah, berversi mengikuti rilis DTSEN.
     */
    public function up(): void
    {
        Schema::create('dtsen_variables', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama', 255);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 100)->nullable()->index();
            $table->string('satuan', 50)->nullable();
            // Level hak akses minimal yang diperlukan untuk memperoleh variabel ini (Bab IV).
            $table->unsignedTinyInteger('level_minimal')->default(2)->index();
            $table->foreignId('dtsen_release_id')->nullable()->constrained('dtsen_releases')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_variables');
    }
};
