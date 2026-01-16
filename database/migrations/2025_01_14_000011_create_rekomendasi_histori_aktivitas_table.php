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
        Schema::create('rekomendasi_histori_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekomendasi_aplikasi_form_id')
                ->constrained('rekomendasi_aplikasi_forms')
                ->onDelete('cascade')
                ->name('rha_aplikasi_form_fk');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('aktivitas'); // e.g., 'Usulan Diajukan', 'Dokumen Diupload'
            $table->text('deskripsi')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('rekomendasi_aplikasi_form_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_histori_aktivitas');
    }
};
