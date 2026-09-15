<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permintaan aktivasi ulang akun yang dinonaktifkan karena tidak digunakan
     * selama 30 hari kalender (Bab III huruf C Juknis).
     */
    public function up(): void
    {
        Schema::create('dtsen_account_reactivations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dtsen_account_request_id')->constrained('dtsen_account_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('alasan');
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan')->index();
            $table->text('catatan')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_account_reactivations');
    }
};
