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
        Schema::create('rekomendasi_dokumen_usulan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekomendasi_aplikasi_form_id')
                ->constrained('rekomendasi_aplikasi_forms')
                ->onDelete('cascade');
            $table->enum('jenis_dokumen', [
                'analisis_kebutuhan',
                'perencanaan',
                'manajemen_risiko'
            ]);
            $table->string('nama_file');
            $table->string('file_path');
            $table->integer('file_size'); // in bytes
            $table->string('mime_type');
            $table->integer('versi')->default(1);
            $table->text('keterangan')->nullable();
            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();

            // Indexes for better performance
            $table->index('rekomendasi_aplikasi_form_id');
            $table->index('jenis_dokumen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_dokumen_usulan');
    }
};
