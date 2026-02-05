<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fix pelaksana_pembangunan ENUM to include all 3 values expected by the form.
     * The form and validation expect: menteri, swakelola, pihak_ketiga
     * But the original migration only defined: swakelola, pihak_ketiga
     * This was causing SQL data truncation error when users selected "Menteri".
     */
    public function up(): void
    {
        Schema::table('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            $table->enum('pelaksana_pembangunan', ['menteri', 'swakelola', 'pihak_ketiga'])
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            // Revert to original (incorrect) ENUM definition
            $table->enum('pelaksana_pembangunan', ['swakelola', 'pihak_ketiga'])
                ->nullable()
                ->change();
        });
    }
};
