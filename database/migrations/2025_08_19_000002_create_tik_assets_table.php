<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tik_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tik_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable()->unique();       // kode internal (opsional)
            $table->string('serial_number')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('condition')->default('baik');       // baik / rusak ringan / rusak berat (free text dulu)
            $table->string('location')->nullable();
            $table->string('photo_path')->nullable();            // 1 foto opsional
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tik_assets');
    }
};
