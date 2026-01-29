<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include 'pemeliharaan'
        DB::statement("ALTER TABLE fase_pengembangan_dokumen MODIFY COLUMN fase ENUM('rancang_bangun', 'implementasi', 'uji_kelaikan', 'pemeliharaan') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE fase_pengembangan_dokumen MODIFY COLUMN fase ENUM('rancang_bangun', 'implementasi', 'uji_kelaikan') NOT NULL");
    }
};
