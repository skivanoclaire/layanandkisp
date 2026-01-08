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
            // Change jenis from enum('Induk','Cabang') to varchar for website categories
            $table->string('jenis', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_monitors', function (Blueprint $table) {
            // Revert back to original enum
            $table->enum('jenis', ['Induk', 'Cabang'])->nullable()->change();
        });
    }
};
