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
        Schema::table('jabatan', function (Blueprint $table) {
            // Add new unit_kerja_id foreign key column
            $table->foreignId('unit_kerja_id')
                  ->nullable()
                  ->after('nama_jabatan')
                  ->constrained('unit_kerjas')
                  ->nullOnDelete();

            $table->index('unit_kerja_id');
        });

        // Rename old unit_kerja column to unit_kerja_legacy (backup)
        Schema::table('jabatan', function (Blueprint $table) {
            $table->renameColumn('unit_kerja', 'unit_kerja_legacy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original column name
        Schema::table('jabatan', function (Blueprint $table) {
            $table->renameColumn('unit_kerja_legacy', 'unit_kerja');
        });

        Schema::table('jabatan', function (Blueprint $table) {
            $table->dropForeign(['unit_kerja_id']);
            $table->dropIndex(['unit_kerja_id']);
            $table->dropColumn('unit_kerja_id');
        });
    }
};
