<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat aktivitas/transisi status untuk seluruh permohonan SPLP (V1–V5).
     * Polymorphic ringan via request_type + request_id agar satu tabel melayani semua jenis.
     */
    public function up(): void
    {
        Schema::create('splp_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('request_type', 50);   // mis. provider, consumer, sandbox, change, deactivation
            $table->unsignedBigInteger('request_id');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['request_type', 'request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('splp_request_logs');
    }
};
