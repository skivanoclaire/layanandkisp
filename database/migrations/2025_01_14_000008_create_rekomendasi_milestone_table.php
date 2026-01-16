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
        Schema::create('rekomendasi_milestone', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekomendasi_fase_pengembangan_id')
                ->constrained('rekomendasi_fase_pengembangan')
                ->onDelete('cascade')
                ->name('rm_fase_pengembangan_fk');
            $table->string('nama_milestone');
            $table->date('target_tanggal');
            $table->enum('status', ['not_started', 'in_progress', 'completed'])
                ->default('not_started');
            $table->string('file_bukti')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('rekomendasi_fase_pengembangan_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_milestone');
    }
};
