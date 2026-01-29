<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update fase_saat_ini for approved proposals
        // Change from 'penandatanganan' to 'menunggu_kementerian'
        // This aligns with the new workflow where approved proposals wait for Ministry approval
        DB::table('rekomendasi_aplikasi_forms')
            ->where('status', 'disetujui')
            ->where('fase_saat_ini', 'penandatanganan')
            ->update(['fase_saat_ini' => 'menunggu_kementerian']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: change back to 'penandatanganan'
        DB::table('rekomendasi_aplikasi_forms')
            ->where('status', 'disetujui')
            ->where('fase_saat_ini', 'menunggu_kementerian')
            ->update(['fase_saat_ini' => 'penandatanganan']);
    }
};
