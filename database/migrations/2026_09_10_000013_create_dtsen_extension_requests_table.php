<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Form 4.4 - Permohonan perpanjangan masa akses token/tautan unduh.
     */
    public function up(): void
    {
        Schema::create('dtsen_extension_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dtsen_data_request_id')->constrained('dtsen_data_requests')->cascadeOnDelete();
            $table->foreignId('dtsen_access_token_id')->nullable()
                ->constrained('dtsen_access_tokens')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('alasan');
            $table->unsignedSmallInteger('durasi_hari');
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan')->index();
            $table->text('catatan')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_extension_requests');
    }
};
