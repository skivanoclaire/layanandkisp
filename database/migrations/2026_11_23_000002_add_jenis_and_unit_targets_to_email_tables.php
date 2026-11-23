<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menandai jenis email (pribadi / unit_kerja / unit_organisasi) dan unit tujuannya,
     * baik di permohonan (email_requests) maupun di master data (email_accounts).
     * Data lama otomatis berjenis 'pribadi'.
     */
    public function up(): void
    {
        Schema::table('email_requests', function (Blueprint $table) {
            $table->string('jenis', 20)->default('pribadi')->after('ticket_no');
            $table->foreignId('unit_kerja_id')->nullable()->after('jenis')
                ->constrained('unit_kerjas')->nullOnDelete();
            $table->foreignId('unit_organisasi_id')->nullable()->after('unit_kerja_id')
                ->constrained('unit_organisasis')->nullOnDelete();
            $table->index('jenis');
        });

        Schema::table('email_accounts', function (Blueprint $table) {
            $table->string('jenis', 20)->default('pribadi')->after('user');
            $table->foreignId('unit_kerja_id')->nullable()->after('jenis')
                ->constrained('unit_kerjas')->nullOnDelete();
            $table->foreignId('unit_organisasi_id')->nullable()->after('unit_kerja_id')
                ->constrained('unit_organisasis')->nullOnDelete();
            $table->index('jenis');
        });
    }

    public function down(): void
    {
        Schema::table('email_accounts', function (Blueprint $table) {
            $table->dropForeign(['unit_kerja_id']);
            $table->dropForeign(['unit_organisasi_id']);
            $table->dropIndex(['jenis']);
            $table->dropColumn(['jenis', 'unit_kerja_id', 'unit_organisasi_id']);
        });

        Schema::table('email_requests', function (Blueprint $table) {
            $table->dropForeign(['unit_kerja_id']);
            $table->dropForeign(['unit_organisasi_id']);
            $table->dropIndex(['jenis']);
            $table->dropColumn(['jenis', 'unit_kerja_id', 'unit_organisasi_id']);
        });
    }
};
