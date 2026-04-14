<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['vpn_registrations', 'vpn_resets', 'visitations'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->boolean('is_kabupaten_kota')->default(false)->after('nip');
                $t->enum('kabupaten_kota', ['Bulungan', 'Malinau', 'Tana Tidung', 'Tarakan', 'Nunukan'])->nullable()->after('is_kabupaten_kota');
                $t->string('unit_kerja_manual')->nullable()->after('kabupaten_kota');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['is_kabupaten_kota', 'kabupaten_kota', 'unit_kerja_manual']);
            });
        }
    }
};
