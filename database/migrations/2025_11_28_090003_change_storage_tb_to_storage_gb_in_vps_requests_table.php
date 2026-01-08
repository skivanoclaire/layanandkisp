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
        Schema::table('vps_requests', function (Blueprint $table) {
            // Convert existing TB values to GB (1 TB = 1000 GB)
            DB::statement('UPDATE vps_requests SET storage_tb = storage_tb * 1000');

            // Rename column from storage_tb to storage_gb
            $table->renameColumn('storage_tb', 'storage_gb');
        });

        // Change column type to integer after rename
        Schema::table('vps_requests', function (Blueprint $table) {
            $table->integer('storage_gb')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Change back to decimal
        Schema::table('vps_requests', function (Blueprint $table) {
            $table->decimal('storage_gb', 8, 2)->change();
        });

        Schema::table('vps_requests', function (Blueprint $table) {
            // Rename column back
            $table->renameColumn('storage_gb', 'storage_tb');

            // Convert GB values back to TB (divide by 1000)
            DB::statement('UPDATE vps_requests SET storage_tb = storage_tb / 1000');
        });
    }
};
