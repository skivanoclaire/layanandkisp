<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fitur 4.3 - Penerbitan token/tautan unduh dengan masa aktif 30 hari kalender.
     * Countdown ditampilkan ke OPD; perpanjangan mengubah `expires_at`.
     */
    public function up(): void
    {
        Schema::create('dtsen_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dtsen_data_request_id')->constrained('dtsen_data_requests')->cascadeOnDelete();
            $table->string('token', 64)->unique()->index();
            $table->enum('metode', ['api', 'excel_terenkripsi', 'vpn'])->default('excel_terenkripsi');
            // Berkas siap unduh (metode excel) atau parameter kanal (api/vpn).
            $table->string('file_path', 255)->nullable();
            $table->string('nama_berkas', 255)->nullable();
            $table->text('parameter')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('expiry_warned_at')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('max_download')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_access_tokens');
    }
};
