<?php

// database/migrations/2025_08_27_000000_create_simpeg_checks_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('simpeg_checks', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // user yang dikomparasi (berdasarkan NIK)
            $table->boolean('is_nik_valid')->default(false);
            $table->string('nip')->nullable();
            $table->string('name_from_simpeg')->nullable();
            $table->boolean('name_match')->default(false);
            $table->boolean('phone_match')->default(false);
            $table->boolean('email_match')->default(false);
            $table->json('raw_response')->nullable();     // simpan respon mentah API
            $table->foreignId('created_by')->constrained('users'); // admin yang cek
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('simpeg_checks');
    }
};
