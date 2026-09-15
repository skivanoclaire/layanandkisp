<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit trail lintas-tahapan DTSEN (pola sama dengan splp_request_logs):
     * satu tabel untuk seluruh jenis berkas, dibedakan oleh `request_type`.
     */
    public function up(): void
    {
        Schema::create('dtsen_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('request_type', 40)->index();
            $table->unsignedBigInteger('request_id')->index();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 120);
            $table->text('note')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['request_type', 'request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_request_logs');
    }
};
