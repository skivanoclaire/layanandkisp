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
        Schema::create('pse_update_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pse_update_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100); // created, submitted, status:X->Y, approved, rejected, etc.
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('pse_update_request_id');
            $table->index('actor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pse_update_request_logs');
    }
};
