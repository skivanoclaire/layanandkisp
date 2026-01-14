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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('unit_kerja_id')
                  ->nullable()
                  ->after('nip')
                  ->constrained('unit_kerjas')
                  ->nullOnDelete();

            $table->index('unit_kerja_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_kerja_id']);
            $table->dropIndex(['unit_kerja_id']);
            $table->dropColumn('unit_kerja_id');
        });
    }
};
