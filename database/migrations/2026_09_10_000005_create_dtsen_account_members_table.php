<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data calon pengguna akun (bisa lebih dari satu personel per OPD) — Lampiran I.
     */
    public function up(): void
    {
        Schema::create('dtsen_account_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dtsen_account_request_id')->constrained('dtsen_account_requests')->cascadeOnDelete();
            // Terisi bila personel sudah punya akun portal (dicocokkan lewat NIP/email).
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama', 150);
            $table->string('nip', 30)->nullable();
            $table->string('jabatan', 150)->nullable();
            $table->string('unit_kerja', 200)->nullable();
            $table->string('no_hp', 30)->nullable();
            $table->string('email', 200);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_account_members');
    }
};
