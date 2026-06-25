<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('splp_services', function (Blueprint $table) {
            $table->id();

            // Identitas layanan
            $table->string('kode_layanan', 50)->unique();
            $table->string('nama_layanan', 200);
            $table->foreignId('opd_pemilik_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->text('deskripsi')->nullable();

            // Konfigurasi teknis (record-keeping; provisioning aktual di gateway)
            $table->string('backend_url', 500)->nullable();
            $table->string('route_path', 255)->nullable();
            $table->enum('environment', ['produksi', 'sandbox'])->default('produksi');
            $table->enum('auth_type', ['apikey', 'oauth2', 'none'])->default('apikey');
            $table->enum('klasifikasi_data', ['publik', 'terbatas', 'rahasia'])->default('publik');

            // Referensi ID di gateway SPLP (diisi admin setelah provisioning manual)
            $table->string('gateway_service_id', 100)->nullable();
            $table->string('gateway_route_id', 100)->nullable();

            // Status registry
            $table->enum('status', ['aktif', 'nonaktif', 'dicabut'])->default('aktif')->index();
            $table->date('tgl_aktif')->nullable();

            // Asal permohonan (V1 provider / V3 sandbox)
            $table->string('source_request_type', 50)->nullable();
            $table->unsignedBigInteger('source_request_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('splp_services');
    }
};
