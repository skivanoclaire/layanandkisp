<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_whitelists', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('ip_address');
        });

        // Seed IP Gateway SPLP (Sistem Penghubung Layanan Pemerintah)
        DB::table('api_whitelists')->insert([
            'ip_address' => '103.170.104.48',
            'description' => 'SPLP Gateway',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_whitelists');
    }
};
