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
        // First, truncate the table to start fresh
        \DB::table('unit_kerjas')->truncate();

        Schema::table('unit_kerjas', function (Blueprint $table) {
            // Drop old enum column
            $table->dropColumn('kategori');

            // Add new tipe column
            $table->enum('tipe', [
                'Induk Perangkat Daerah',
                'Cabang Perangkat Daerah',
                'Sekolah'
            ])->after('singkatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::table('unit_kerjas')->truncate();

        Schema::table('unit_kerjas', function (Blueprint $table) {
            $table->dropColumn('tipe');

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
            ])->default('Lainnya')->after('singkatan');
        });
    }
};
