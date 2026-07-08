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
        Schema::create('survei_digital_settings', function (Blueprint $table) {
            $table->id();
            // URL embed survei sampai dengan segmen ".../embed/view/" (tanpa query string).
            // Bagian ini memuat token yang dipakai bersama seluruh layanan.
            $table->text('embed_base_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Seed satu baris default dengan token terbaru.
        \DB::table('survei_digital_settings')->insert([
            'embed_base_url' => 'https://surveidigital.spbe.go.id/embed/survey/eyJzdXJ2ZXlfaWQiOjIsInNlcnZpY2VfaWQiOjE2MCwiaG9zdCI6ImxheWFuYW4uZGlza29taW5mby5rYWx0YXJhcHJvdi5nby5pZCxsb2NhbGhvc3Q6ODA4MCxsb2NhbGhvc3QsaHR0cHM6Ly9sYXlhbmFuLmRpc2tvbWluZm8ua2FsdGFyYXByb3YuZ28uaWQiLCJrZXkiOiJ2THNrc1VWeSJ9/embed/view/',
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
        Schema::dropIfExists('survei_digital_settings');
    }
};
