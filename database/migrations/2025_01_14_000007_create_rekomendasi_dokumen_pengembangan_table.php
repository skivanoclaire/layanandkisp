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
        Schema::create('rekomendasi_dokumen_pengembangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekomendasi_fase_pengembangan_id')
                ->constrained('rekomendasi_fase_pengembangan')
                ->onDelete('cascade')
                ->name('rdp_fase_pengembangan_fk');
            $table->string('jenis_dokumen'); // 'BRD', 'SRS', 'Wireframe', 'ERD', etc.
            $table->enum('kategori', [
                'dokumentasi',
                'timeline',
                'tim',
                'pengembangan',
                'instalasi',
                'antarmuka',
                'sosialisasi',
                'serah_terima',
                'testing'
            ]);
            $table->string('nama_file');
            $table->string('file_path');
            $table->integer('file_size'); // in bytes
            $table->string('mime_type');
            $table->text('keterangan')->nullable();
            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index('rekomendasi_fase_pengembangan_id', 'rdp_fase_pengembangan_idx');
            $table->index('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_dokumen_pengembangan');
    }
};
