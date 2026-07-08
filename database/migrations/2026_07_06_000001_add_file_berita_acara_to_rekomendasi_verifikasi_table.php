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
        Schema::table('rekomendasi_verifikasi', function (Blueprint $table) {
            $table->string('file_berita_acara')->nullable()->after('file_kajian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekomendasi_verifikasi', function (Blueprint $table) {
            $table->dropColumn('file_berita_acara');
        });
    }
};
