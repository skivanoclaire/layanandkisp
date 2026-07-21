<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menandai asal setiap tanggal libur.
     *
     * `sumber` dipakai agar impor dari API libur nasional tidak pernah menimpa
     * atau menghapus tanggal yang diketik admin (mis. libur daerah), yang tidak
     * akan pernah muncul di API mana pun.
     */
    public function up(): void
    {
        Schema::table('sla_holidays', function (Blueprint $table) {
            $table->enum('sumber', ['manual', 'import'])->default('manual')->after('keterangan');
            $table->enum('jenis', ['libur_nasional', 'cuti_bersama'])->nullable()->after('sumber');
        });
    }

    public function down(): void
    {
        Schema::table('sla_holidays', function (Blueprint $table) {
            $table->dropColumn(['sumber', 'jenis']);
        });
    }
};
