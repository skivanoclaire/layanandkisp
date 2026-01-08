<?php

// database/migrations/2025_08_19_100002_create_tik_borrowing_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tik_borrowing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tik_borrowing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tik_asset_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();
            $table->unique(['tik_borrowing_id','tik_asset_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('tik_borrowing_items');
    }
};
