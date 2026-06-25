<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * V1 — Pendaftaran Endpoint Penyedia Layanan (Service Provider).
     */
    public function up(): void
    {
        Schema::create('splp_provider_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no', 30)->unique()->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Pemohon
            $table->string('nama', 200);
            $table->string('nip', 30)->nullable();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->string('email_pemohon', 200);
            $table->string('no_hp', 30);

            // Detail layanan yang didaftarkan
            $table->string('nama_layanan', 200);
            $table->text('deskripsi')->nullable();
            $table->string('backend_url', 500);
            $table->string('route_path', 255)->nullable();
            $table->enum('auth_type', ['apikey', 'oauth2', 'none'])->default('apikey');
            $table->enum('klasifikasi_data', ['publik', 'terbatas', 'rahasia'])->default('publik');

            // Klasifikasi keamanan data (opsional, pola dc_* subdomain)
            $table->enum('dc_confidentiality', ['Rendah', 'Sedang', 'Tinggi'])->nullable();
            $table->enum('dc_integrity', ['Rendah', 'Sedang', 'Tinggi'])->nullable();
            $table->enum('dc_availability', ['Rendah', 'Sedang', 'Tinggi'])->nullable();

            // Lampiran
            $table->string('surat_permohonan_path', 255)->nullable();
            $table->string('openapi_doc_path', 255)->nullable();

            // Hasil provisioning (record-keeping)
            $table->foreignId('splp_service_id')->nullable()->constrained('splp_services')->nullOnDelete();

            // Siklus hidup
            $table->enum('status', [
                'draft', 'diajukan', 'verifikasi_administrasi', 'verifikasi_teknis',
                'menunggu_keputusan', 'disetujui', 'selesai', 'ditolak', 'perlu_perbaikan',
            ])->default('diajukan')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verif_admin_at')->nullable();
            $table->timestamp('verif_teknis_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // Verifikasi & catatan admin
            $table->boolean('check_administrasi')->default(false);
            $table->boolean('check_teknis')->default(false);
            $table->boolean('check_dokumentasi')->default(false);
            $table->boolean('check_klasifikasi_data')->default(false);
            $table->text('catatan_verifikasi')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('consent_true')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('splp_provider_requests');
    }
};
