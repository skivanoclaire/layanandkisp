<?php

// database/migrations/2025_08_19_100001_create_tik_borrowings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tik_borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('users')->cascadeOnDelete();
            $table->string('code')->unique();   // TIK-YYYYMMDD-XXXX
            $table->enum('status', ['pending','ongoing','returned'])->default('ongoing');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('notes')->nullable();  // catatan umum
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tik_borrowings');
    }
};
