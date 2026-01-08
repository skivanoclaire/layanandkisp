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
        Schema::table('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            $table->unsignedBigInteger('pemilik_proses_bisnis_id')->nullable()->after('pihak_terkait');
            $table->json('stakeholder_internal')->nullable()->after('pemilik_proses_bisnis_id');
            $table->text('stakeholder_eksternal')->nullable()->after('stakeholder_internal');

            $table->foreign('pemilik_proses_bisnis_id')->references('id')->on('unit_kerjas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            $table->dropForeign(['pemilik_proses_bisnis_id']);
            $table->dropColumn(['pemilik_proses_bisnis_id', 'stakeholder_internal', 'stakeholder_eksternal']);
        });
    }
};
