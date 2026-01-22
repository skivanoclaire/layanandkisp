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
        Schema::create('survei_kepuasan_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subdomain_request_id')->constrained('subdomain_requests')->onDelete('cascade');
            
            // Rating questions (1-5 scale)
            $table->tinyInteger('rating_kecepatan')->comment('Kecepatan akses layanan (1=Buruk, 5=Sangat Baik)');
            $table->tinyInteger('rating_kemudahan')->comment('Kemudahan penggunaan (1=Buruk, 5=Sangat Baik)');
            $table->tinyInteger('rating_kualitas')->comment('Kualitas layanan (1=Buruk, 5=Sangat Baik)');
            $table->tinyInteger('rating_responsif')->comment('Responsivitas layanan (1=Buruk, 5=Sangat Baik)');
            $table->tinyInteger('rating_keamanan')->comment('Keamanan layanan (1=Buruk, 5=Sangat Baik)');
            $table->tinyInteger('rating_keseluruhan')->comment('Kepuasan keseluruhan (1=Buruk, 5=Sangat Baik)');
            
            // Optional feedback
            $table->text('saran')->nullable()->comment('Saran perbaikan');
            $table->text('kelebihan')->nullable()->comment('Kelebihan layanan');
            $table->text('kekurangan')->nullable()->comment('Kekurangan layanan');
            
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['subdomain_request_id', 'created_at']);
            $table->index('user_id');
            
            // Prevent duplicate survey from same user for same subdomain
            $table->unique(['user_id', 'subdomain_request_id'], 'unique_user_subdomain_survey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survei_kepuasan_layanan');
    }
};
