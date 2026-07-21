<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Target SLA per layanan (end-to-end: dari permohonan masuk s.d. selesai/ditolak).
     * Baris per `service_key` diisi otomatis dari App\Services\Sla\SlaServiceRegistry
     * saat halaman pengaturan SLA pertama kali dibuka (lihat SlaSetting::ensureDefaults()).
     */
    public function up(): void
    {
        Schema::create('sla_settings', function (Blueprint $table) {
            $table->id();
            $table->string('service_key')->unique();
            $table->string('label');
            $table->unsignedInteger('target_value')->default(3);
            $table->enum('target_unit', ['jam', 'hari_kerja'])->default('hari_kerja');
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_settings');
    }
};
