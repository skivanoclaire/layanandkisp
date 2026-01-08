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
        Schema::create('email_password_reset_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('email_address'); // Email yang akan direset (dari NIP)
            $table->string('nip', 20);
            $table->string('encrypted_password'); // Password baru yang sudah dienkrip
            $table->enum('status', ['pending', 'processed', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable(); // Catatan dari admin
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null'); // Admin yang memproses
            $table->timestamp('processed_at')->nullable();
            $table->enum('reset_method', ['manual', 'api'])->default('manual'); // Metode reset
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_password_reset_requests');
    }
};
