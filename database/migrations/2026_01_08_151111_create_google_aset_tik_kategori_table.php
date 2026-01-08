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
        Schema::create('google_aset_tik_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perangkat')->unique();
            $table->string('kategori_perangkat');
            $table->timestamps();

            $table->index('kategori_perangkat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_aset_tik_kategori');
    }
};
