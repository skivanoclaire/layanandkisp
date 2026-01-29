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
            // Add missing checklist columns if not exist
            if (!Schema::hasColumn('rekomendasi_verifikasi', 'checklist_kelengkapan_data')) {
                $table->boolean('checklist_kelengkapan_data')->default(false)->after('checklist_manajemen_risiko');
            }

            if (!Schema::hasColumn('rekomendasi_verifikasi', 'checklist_kesesuaian_peraturan')) {
                $table->boolean('checklist_kesesuaian_peraturan')->default(false)->after('checklist_kelengkapan_data');
            }

            // Add catatan_internal column if not exists
            if (!Schema::hasColumn('rekomendasi_verifikasi', 'catatan_internal')) {
                $table->text('catatan_internal')->nullable()->after('catatan_verifikasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekomendasi_verifikasi', function (Blueprint $table) {
            $table->dropColumn(['checklist_kelengkapan_data', 'checklist_kesesuaian_peraturan']);

            if (Schema::hasColumn('rekomendasi_verifikasi', 'catatan_internal')) {
                $table->dropColumn('catatan_internal');
            }
        });
    }
};
