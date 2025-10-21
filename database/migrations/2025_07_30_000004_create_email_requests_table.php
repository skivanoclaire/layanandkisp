<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_email_requests_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('email_requests', function (Blueprint $t) {
      $t->id();
      $t->string('ticket_no')->unique()->index();            // EML-YYYY-XXXXXX
      $t->foreignId('user_id')->constrained()->cascadeOnDelete();

      // Field formulir (1–6)
      $t->string('nama');
      $t->string('nip')->nullable();
      $t->string('instansi')->nullable();
      $t->string('username');                                 // usulan @kaltaraprov.go.id
      $t->string('email_alternatif')->nullable();
      $t->string('no_hp')->nullable();

      // Password untuk provisioning manual (disimpan terenkripsi)
      $t->text('password_encrypted');                         // disimpan via Crypt::encryptString()
      $t->boolean('consent_true')->default(false);            // setuju syarat/keabsahan data

      // Status: menunggu, proses, ditolak, selesai
      $t->enum('status', ['menunggu','proses','ditolak','selesai'])->default('menunggu')->index();

      // jejak waktu proses
      $t->timestamp('submitted_at')->nullable();
      $t->timestamp('processing_at')->nullable();
      $t->timestamp('rejected_at')->nullable();
      $t->timestamp('completed_at')->nullable();

      $t->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('email_requests');
  }
};
