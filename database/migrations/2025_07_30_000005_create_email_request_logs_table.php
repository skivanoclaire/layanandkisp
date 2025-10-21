<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_email_request_logs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('email_request_logs', function (Blueprint $t) {
      $t->id();
      $t->foreignId('email_request_id')->constrained()->cascadeOnDelete();
      $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete(); // admin/user
      $t->string('action');     // created / status:menunggu->proses / catatan dll
      $t->text('note')->nullable();
      $t->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('email_request_logs');
  }
};
