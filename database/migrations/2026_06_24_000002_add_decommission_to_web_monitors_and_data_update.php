<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penanda "pensiun"/non-aktif aplikasi yang TERPISAH dari kolom `status`
     * (status diisi otomatis tiap jam oleh website:check-status). Data tidak
     * pernah dihapus — hanya ditandai agar tetap tersimpan untuk pemeriksaan.
     */
    public function up(): void
    {
        Schema::table('web_monitors', function (Blueprint $table) {
            $table->boolean('is_decommissioned')->default(false)->after('status');
            $table->timestamp('decommissioned_at')->nullable()->after('is_decommissioned');
        });

        Schema::table('subdomain_data_update_requests', function (Blueprint $table) {
            // null = tidak ada usulan status; 1 = usul non-aktifkan; 0 = usul aktifkan kembali
            $table->boolean('proposed_decommission')->nullable()->after('proposed_data');
        });
    }

    public function down(): void
    {
        Schema::table('web_monitors', function (Blueprint $table) {
            $table->dropColumn(['is_decommissioned', 'decommissioned_at']);
        });

        Schema::table('subdomain_data_update_requests', function (Blueprint $table) {
            $table->dropColumn('proposed_decommission');
        });
    }
};
