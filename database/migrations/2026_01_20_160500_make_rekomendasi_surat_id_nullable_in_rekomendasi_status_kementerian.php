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
        Schema::table('rekomendasi_status_kementerian', function (Blueprint $table) {
            // Make rekomendasi_surat_id nullable to support new workflow
            // New workflow uses rekomendasi_aplikasi_form_id directly
            // Old workflow (legacy) uses rekomendasi_surat_id
            $table->unsignedBigInteger('rekomendasi_surat_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekomendasi_status_kementerian', function (Blueprint $table) {
            // Revert to NOT NULL (this may fail if there are NULL values)
            $table->unsignedBigInteger('rekomendasi_surat_id')->nullable(false)->change();
        });
    }
};
