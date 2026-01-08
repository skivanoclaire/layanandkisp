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
        Schema::create('google_aset_tik_software', function (Blueprint $table) {
            $table->id();

            // Data dari spreadsheet (26 kolom)
            $table->integer('no')->nullable();
            $table->string('nama_opd');
            $table->string('nama_aset')->nullable();
            $table->string('kode_barang')->nullable();
            $table->integer('tahun')->nullable();
            $table->string('judul')->nullable();
            $table->decimal('harga', 15, 2)->default(0);
            $table->string('is_aktif')->nullable();
            $table->text('keterangan_software')->nullable();
            $table->string('jenis_perangkat_lunak')->nullable();
            $table->string('data_output')->nullable();
            $table->string('pengembangan')->nullable();
            $table->string('sewa')->nullable();
            $table->string('software_berjalan')->nullable();
            $table->string('fitur_sesuai')->nullable();
            $table->string('url')->nullable();
            $table->string('integrasi')->nullable();
            $table->string('platform')->nullable();
            $table->string('database')->nullable();
            $table->string('script')->nullable();
            $table->string('framework')->nullable();
            $table->string('status')->nullable();
            $table->string('terotorisasi')->nullable();
            $table->string('aset_vital')->nullable();
            $table->text('keterangan_utilisasi')->nullable();
            $table->string('asal_usul')->nullable();

            // Tracking sync
            $table->integer('spreadsheet_row')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->enum('sync_status', ['synced', 'pending_export', 'conflict', 'local_only'])->default('synced');
            $table->text('sync_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('nama_opd');
            $table->index('jenis_perangkat_lunak');
            $table->index('tahun');
            $table->index('is_aktif');
            $table->index('sync_status');
            $table->index('spreadsheet_row');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_aset_tik_software');
    }
};
