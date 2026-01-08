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
        Schema::table('vidcon_data', function (Blueprint $table) {
            // Add missing fields from vidcon_requests
            $table->string('nama_pemohon')->nullable()->after('nomor_surat');
            $table->string('nip_pemohon')->nullable()->after('nama_pemohon');
            $table->string('email_pemohon')->nullable()->after('nip_pemohon');
            $table->string('no_hp')->nullable()->after('email_pemohon');
            $table->foreignId('unit_kerja_id')->nullable()->after('no_hp')->constrained('unit_kerjas')->onDelete('set null');

            // Meeting details
            $table->string('link_meeting')->nullable()->after('platform');
            $table->string('meeting_id')->nullable()->after('link_meeting');
            $table->string('meeting_password')->nullable()->after('meeting_id');
            $table->text('informasi_tambahan')->nullable()->after('meeting_password');
            $table->integer('jumlah_peserta')->nullable()->after('informasi_tambahan');
            $table->text('keperluan_khusus')->nullable()->after('jumlah_peserta');
            $table->text('deskripsi_kegiatan')->nullable()->after('judul_kegiatan');

            // Reference to vidcon_request
            $table->foreignId('vidcon_request_id')->nullable()->after('id')->constrained('vidcon_requests')->onDelete('set null');

            // Workflow tracking
            $table->foreignId('processed_by')->nullable()->after('keterangan')->constrained('users')->onDelete('set null');
            $table->timestamp('completed_at')->nullable()->after('processed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vidcon_data', function (Blueprint $table) {
            $table->dropForeign(['unit_kerja_id']);
            $table->dropForeign(['vidcon_request_id']);
            $table->dropForeign(['processed_by']);

            $table->dropColumn([
                'nama_pemohon',
                'nip_pemohon',
                'email_pemohon',
                'no_hp',
                'unit_kerja_id',
                'link_meeting',
                'meeting_id',
                'meeting_password',
                'informasi_tambahan',
                'jumlah_peserta',
                'keperluan_khusus',
                'deskripsi_kegiatan',
                'vidcon_request_id',
                'processed_by',
                'completed_at',
            ]);
        });
    }
};
