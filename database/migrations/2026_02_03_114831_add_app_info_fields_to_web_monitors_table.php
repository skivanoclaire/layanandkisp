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
            $table->text('description')->nullable()->after('nama_aplikasi');
            $table->text('latar_belakang')->nullable()->after('description');
            $table->text('manfaat_aplikasi')->nullable()->after('latar_belakang');
            $table->integer('tahun_pembuatan')->nullable()->after('manfaat_aplikasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_monitors', function (Blueprint $table) {
            $table->dropColumn(['description', 'latar_belakang', 'manfaat_aplikasi', 'tahun_pembuatan']);
        });
    }
};
