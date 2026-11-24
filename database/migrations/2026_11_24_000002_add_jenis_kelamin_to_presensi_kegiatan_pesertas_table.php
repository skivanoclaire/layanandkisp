<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensi_kegiatan_pesertas', function (Blueprint $table) {
            $table->string('jenis_kelamin', 20)->nullable()->after('nip');
        });
    }

    public function down(): void
    {
        Schema::table('presensi_kegiatan_pesertas', function (Blueprint $table) {
            $table->dropColumn('jenis_kelamin');
        });
    }
};
