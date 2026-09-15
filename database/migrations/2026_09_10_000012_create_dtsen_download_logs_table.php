<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Log unduhan (audit trail: siapa, kapan, dari mana) - mendukung pemantauan
     * traffic & audit hak akses, Bab VIII huruf B Juknis.
     */
    public function up(): void
    {
        Schema::create('dtsen_download_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dtsen_access_token_id')->constrained('dtsen_access_tokens')->cascadeOnDelete();
            $table->foreignId('dtsen_data_request_id')->constrained('dtsen_data_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('downloaded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_download_logs');
    }
};
