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
        Schema::create('rekomendasi_fase_pengembangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekomendasi_aplikasi_form_id')
                ->constrained('rekomendasi_aplikasi_forms')
                ->onDelete('cascade')
                ->name('rfp_aplikasi_form_fk');
            $table->enum('fase', [
                'rancang_bangun',
                'implementasi',
                'uji_kelaikan',
                'pemeliharaan',
                'evaluasi'
            ]);
            $table->enum('status', ['belum_mulai', 'sedang_berjalan', 'selesai'])
                ->default('belum_mulai');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->integer('progress_persen')->default(0); // 0-100
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('rekomendasi_aplikasi_form_id');
            $table->index('fase');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_fase_pengembangan');
    }
};
