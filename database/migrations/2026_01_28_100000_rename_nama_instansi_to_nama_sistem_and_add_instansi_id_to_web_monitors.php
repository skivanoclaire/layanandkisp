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
        Schema::table('web_monitors', function (Blueprint $table) {
            // Rename nama_instansi to nama_sistem
            $table->renameColumn('nama_instansi', 'nama_sistem');

            // Add instansi_id foreign key to unit_kerjas
            $table->foreignId('instansi_id')->nullable()->after('id')->constrained('unit_kerjas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_monitors', function (Blueprint $table) {
            // Drop foreign key and column
            $table->dropForeign(['instansi_id']);
            $table->dropColumn('instansi_id');

            // Rename back to nama_instansi
            $table->renameColumn('nama_sistem', 'nama_instansi');
        });
    }
};
