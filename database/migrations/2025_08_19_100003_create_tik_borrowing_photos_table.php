<?php

// database/migrations/2025_08_19_100003_create_tik_borrowing_photos_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tik_borrowing_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tik_borrowing_id')->constrained()->cascadeOnDelete();
            $table->enum('phase', ['checkout','return'])->default('checkout');
            $table->string('path');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tik_borrowing_photos');
    }
};
