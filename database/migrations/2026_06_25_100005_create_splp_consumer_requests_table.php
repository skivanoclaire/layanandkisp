<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * V2 — Pendaftaran Akses Konsumen Layanan (Service Consumer).
     */
    public function up(): void
    {
        Schema::create('splp_consumer_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no', 30)->unique()->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Pemohon
            $table->string('nama', 200);
            $table->string('nip', 30)->nullable();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->string('email_pemohon', 200);
            $table->string('no_hp', 30);

            // Layanan tujuan (harus sudah terdaftar & aktif)
            $table->foreignId('splp_service_id')->constrained('splp_services')->cascadeOnDelete();

            // Instansi konsumen + flag eksternal
            $table->foreignId('instansi_konsumen_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->boolean('is_eksternal')->default(false);

            // Detail akses
            $table->text('ip_domain')->nullable();
            $table->string('estimasi_volume', 100)->nullable();
            $table->enum('volume_satuan', ['per_hari', 'per_bulan'])->nullable();
            $table->enum('credential_pref', ['mengikuti_layanan', 'apikey', 'oauth2'])->default('mengikuti_layanan');
            $table->text('tujuan_penggunaan')->nullable();

            // Lampiran
            $table->string('surat_permohonan_path', 255)->nullable();
            $table->string('pks_path', 255)->nullable();
            $table->string('hasil_uji_path', 255)->nullable();

            // Hasil provisioning (record-keeping)
            $table->foreignId('splp_consumer_id')->nullable()->constrained('splp_consumers')->nullOnDelete();

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
            $table->boolean('check_koordinasi_opd')->default(false);
            $table->boolean('check_teknis')->default(false);
            $table->boolean('check_legalitas_data')->default(false);
            $table->text('catatan_verifikasi')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('consent_true')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('splp_consumer_requests');
    }
};
