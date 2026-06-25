<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('splp_service_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('splp_service_id')->nullable()->constrained('splp_services')->cascadeOnDelete();
            $table->foreignId('splp_consumer_id')->nullable()->constrained('splp_consumers')->cascadeOnDelete();

            $table->string('action', 100);
            $table->json('config_lama')->nullable();
            $table->json('config_baru')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('splp_service_logs');
    }
};
