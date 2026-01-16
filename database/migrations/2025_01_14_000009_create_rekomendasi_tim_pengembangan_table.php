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
        Schema::create('rekomendasi_tim_pengembangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekomendasi_aplikasi_form_id')
                ->constrained('rekomendasi_aplikasi_forms')
                ->onDelete('cascade')
                ->name('rtp_aplikasi_form_fk');
            $table->string('nama');
            $table->string('peran'); // 'Project Manager', 'Developer', 'Designer', 'QA', etc.
            $table->string('kontak')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('rekomendasi_aplikasi_form_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_tim_pengembangan');
    }
};
