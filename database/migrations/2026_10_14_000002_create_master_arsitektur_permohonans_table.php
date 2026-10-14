<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permohonan pembaruan Master Data Arsitektur oleh pengguna instansi.
     * Perubahan baru masuk ke master_arsitektur_items setelah disetujui admin.
     * "data" dan "data_lama" berisi nilai datar: kunci kolom config => nilai.
     */
    public function up(): void
    {
        Schema::create('master_arsitektur_permohonans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('unit_kerja_id')->constrained('unit_kerjas')->cascadeOnDelete();
            // Kosong untuk permohonan tambah sampai disetujui
            $table->foreignId('item_id')->nullable()->constrained('master_arsitektur_items')->nullOnDelete();
            $table->string('aksi', 20); // tambah, ubah
            $table->string('jenis', 40);
            $table->json('data');
            $table->json('data_lama')->nullable();
            $table->text('catatan_pemohon')->nullable();
            $table->string('status', 20)->default('menunggu'); // menunggu, disetujui, ditolak
            $table->text('catatan_admin')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diproses_pada')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['unit_kerja_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_arsitektur_permohonans');
    }
};
