<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('splp_consumers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('splp_service_id')->constrained('splp_services')->cascadeOnDelete();
            $table->foreignId('instansi_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->string('nama_konsumen', 200);

            // Metadata kredensial — BUKAN secret mentah (lihat peringatan SOP "kanal aman")
            $table->enum('credential_type', ['apikey', 'oauth2'])->default('apikey');
            $table->string('credential_ref', 200)->nullable();
            $table->string('acl', 200)->nullable();
            $table->string('rate_limit', 100)->nullable();
            $table->text('ip_whitelist')->nullable();
            $table->enum('environment', ['produksi', 'sandbox'])->default('produksi');

            $table->timestamp('expires_at')->nullable(); // penting untuk sandbox
            $table->enum('status', ['aktif', 'nonaktif', 'dicabut', 'kadaluarsa'])->default('aktif')->index();

            // Asal permohonan (V2 consumer / V3 sandbox)
            $table->string('source_request_type', 50)->nullable();
            $table->unsignedBigInteger('source_request_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('splp_consumers');
    }
};
