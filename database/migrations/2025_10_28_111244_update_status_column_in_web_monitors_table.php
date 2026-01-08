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
            // Change status from enum('Aktif','Tidak Aktif') to varchar
            $table->string('status', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_monitors', function (Blueprint $table) {
            // Revert back to original enum
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->nullable()->change();
        });
    }
};
