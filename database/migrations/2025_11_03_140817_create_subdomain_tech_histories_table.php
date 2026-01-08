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
        Schema::create('subdomain_tech_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('web_monitor_id')->constrained()->cascadeOnDelete();
            $table->string('changed_field', 100); // programming_language, framework, database, etc
            $table->text('old_value')->nullable(); // JSON or text
            $table->text('new_value')->nullable(); // JSON or text
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable(); // Alasan perubahan
            $table->timestamps();

            $table->index('web_monitor_id');
            $table->index('changed_by');
            $table->index('changed_field');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subdomain_tech_histories');
    }
};
