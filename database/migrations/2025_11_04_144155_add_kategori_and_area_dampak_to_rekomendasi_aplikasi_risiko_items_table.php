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
        Schema::table('rekomendasi_risiko_items', function (Blueprint $table) {
            $table->string('kategori_risiko_spbe')->nullable()->after('jenis_risiko');
            $table->string('area_dampak_risiko_spbe')->nullable()->after('kategori_risiko_spbe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekomendasi_risiko_items', function (Blueprint $table) {
            $table->dropColumn(['kategori_risiko_spbe', 'area_dampak_risiko_spbe']);
        });
    }
};
