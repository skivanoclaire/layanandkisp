<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, handle NULL values - set default to 'internal' for old records
        DB::statement("UPDATE rekomendasi_aplikasi_forms SET jenis_layanan = 'internal' WHERE jenis_layanan IS NULL");

        // Then update existing data to match new ENUM values
        // Map old values to new values:
        // 'eksternal' -> 'publik' (external services are typically public)
        // 'hybrid' -> 'publik' (hybrid can be considered public)
        // 'internal' stays as 'internal'
        DB::statement("UPDATE rekomendasi_aplikasi_forms SET jenis_layanan = 'publik' WHERE jenis_layanan IN ('eksternal', 'hybrid')");

        // Now modify the ENUM column to accept only new values
        DB::statement("ALTER TABLE rekomendasi_aplikasi_forms MODIFY COLUMN jenis_layanan ENUM('publik', 'internal') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback to old ENUM values
        DB::statement("ALTER TABLE rekomendasi_aplikasi_forms MODIFY COLUMN jenis_layanan ENUM('internal', 'eksternal', 'hybrid') NOT NULL");
    }
};
