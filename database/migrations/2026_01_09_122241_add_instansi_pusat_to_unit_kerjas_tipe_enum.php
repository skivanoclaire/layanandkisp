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
        // Modify the tipe enum to add 'Instansi Pusat/Lainnya'
        \DB::statement("ALTER TABLE unit_kerjas MODIFY COLUMN tipe ENUM('Induk Perangkat Daerah', 'Cabang Perangkat Daerah', 'Sekolah', 'Instansi Pusat/Lainnya')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'Instansi Pusat/Lainnya' from enum
        \DB::statement("ALTER TABLE unit_kerjas MODIFY COLUMN tipe ENUM('Induk Perangkat Daerah', 'Cabang Perangkat Daerah', 'Sekolah')");
    }
};
