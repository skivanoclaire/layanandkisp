<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kapasitas yang disetujui admin (bisa berbeda dari kapasitas_gb yang diminta pemohon)
     * beserta riwayat revisinya yang ditampilkan kepada pemohon.
     */
    public function up(): void
    {
        Schema::table('cloud_storage_requests', function (Blueprint $table) {
            $table->unsignedInteger('kapasitas_disetujui_gb')->nullable()->after('kapasitas_gb');
        });

        // Permohonan yang sudah selesai dianggap disetujui sesuai kapasitas yang diminta
        DB::table('cloud_storage_requests')
            ->where('status', 'selesai')
            ->update(['kapasitas_disetujui_gb' => DB::raw('kapasitas_gb')]);

        Schema::create('cloud_storage_kapasitas_revisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cloud_storage_request_id')->constrained('cloud_storage_requests')->cascadeOnDelete();
            $table->unsignedInteger('kapasitas_lama_gb');
            $table->unsignedInteger('kapasitas_baru_gb');
            $table->text('alasan');
            $table->foreignId('revised_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cloud_storage_kapasitas_revisis');

        Schema::table('cloud_storage_requests', function (Blueprint $table) {
            $table->dropColumn('kapasitas_disetujui_gb');
        });
    }
};
