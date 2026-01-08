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
        Schema::create('google_aset_tik_hardware', function (Blueprint $table) {
            $table->id();

            // Data dari spreadsheet (19 kolom)
            $table->integer('no')->nullable()->comment('Nomor urut di spreadsheet');
            $table->string('nama_opd');
            $table->string('nama_aset');
            $table->string('kode_gab_barang')->nullable();
            $table->string('no_register')->nullable();
            $table->integer('total')->default(1)->comment('Jumlah unit jika range');
            $table->string('merk_type')->nullable();
            $table->integer('tahun')->nullable();
            $table->decimal('nilai_perolehan', 15, 2)->default(0);
            $table->string('jenis_aset_tik')->nullable();
            $table->string('sumber_pendanaan')->nullable();
            $table->string('keadaan_barang')->nullable();
            $table->year('tanggal_perolehan')->nullable();
            $table->date('tanggal_penyerahan')->nullable();
            $table->string('asal_usul')->nullable();
            $table->string('status')->nullable();
            $table->string('terotorisasi')->nullable();
            $table->string('aset_vital')->nullable();
            $table->text('keterangan')->nullable();

            // Tracking untuk sync
            $table->integer('spreadsheet_row')->nullable()->comment('Nomor baris di spreadsheet (untuk mapping balik)');
            $table->timestamp('synced_at')->nullable()->comment('Terakhir sync dari/ke spreadsheet');
            $table->enum('sync_status', ['synced', 'pending_export', 'conflict', 'local_only'])
                  ->default('synced')
                  ->comment('Status sinkronisasi');
            $table->text('sync_notes')->nullable()->comment('Catatan konflik atau error');

            $table->timestamps();
            $table->softDeletes();

            // Indexes untuk performa query
            $table->index('nama_opd');
            $table->index('jenis_aset_tik');
            $table->index('tahun');
            $table->index('keadaan_barang');
            $table->index('sync_status');
            $table->index('spreadsheet_row');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_aset_tik_hardware');
    }
};
