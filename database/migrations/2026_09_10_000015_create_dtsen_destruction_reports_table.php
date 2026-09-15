<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Form 5.2 - Berita Acara Pemusnahan Data (Bab VII huruf C).
     * Salinan BA wajib disampaikan ke DKISP maks. 14 hari kalender sejak pemusnahan;
     * `batas_penyampaian` dipakai sebagai dasar reminder otomatis.
     */
    public function up(): void
    {
        Schema::create('dtsen_destruction_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dtsen_data_request_id')->constrained('dtsen_data_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('dasar_pemusnahan', [
                'habis_retensi', 'permintaan_pengendali', 'permintaan_subjek_data',
                'digantikan_rilis_terbaru', 'pemanfaatan_selesai',
            ])->default('habis_retensi');
            $table->text('metode_pemusnahan');
            $table->dateTime('waktu_pelaksanaan');
            $table->date('batas_penyampaian')->nullable()->index();
            $table->string('petugas_nama', 150);
            $table->string('petugas_nip', 30)->nullable();
            $table->string('saksi_nama', 150)->nullable();
            $table->string('saksi_unit', 200)->nullable();
            $table->string('file_path', 255)->nullable();
            $table->enum('status', ['draft', 'dilaporkan', 'diverifikasi', 'perlu_perbaikan'])
                ->default('draft')->index();
            $table->timestamp('reported_at')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_destruction_reports');
    }
};
