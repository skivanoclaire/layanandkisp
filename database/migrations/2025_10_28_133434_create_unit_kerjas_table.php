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
        Schema::create('unit_kerjas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 255);
            $table->string('singkatan', 50)->nullable();
            $table->enum('kategori', [
                'Badan',
                'Dinas',
                'Sekretariat',
                'Inspektorat',
                'Satpol PP',
                'RSUD',
                'UPT',
                'Cabang',
                'Biro',
                'Sekolah',
                'Lainnya'
            ])->default('Lainnya');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_kerjas');
    }
};
