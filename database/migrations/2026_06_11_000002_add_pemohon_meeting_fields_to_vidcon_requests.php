<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Informasi meeting yang disediakan SENDIRI oleh pemohon — dipakai ketika
     * jenis_layanan = "operator" (Operator saja), di mana pemohon sudah punya
     * link/ID meeting (misal dari Pusat) dan hanya butuh operator.
     * Berbeda dari link_meeting/meeting_id/meeting_password yang diisi admin.
     */
    public function up(): void
    {
        Schema::table('vidcon_requests', function (Blueprint $table) {
            $table->text('pemohon_link_meeting')->nullable()->after('keperluan_khusus');
            $table->string('pemohon_meeting_id')->nullable()->after('pemohon_link_meeting');
            $table->string('pemohon_meeting_password')->nullable()->after('pemohon_meeting_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vidcon_requests', function (Blueprint $table) {
            $table->dropColumn(['pemohon_link_meeting', 'pemohon_meeting_id', 'pemohon_meeting_password']);
        });
    }
};
