<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * V3 — Permohonan Uji Coba (Sandbox) Integrasi SPLP.
     */
    public function up(): void
    {
        Schema::create('splp_sandbox_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no', 30)->unique()->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Pemohon
            $table->string('nama', 200);
            $table->string('nip', 30)->nullable();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->string('email_pemohon', 200);
            $table->string('no_hp', 30);

            // Detail uji coba
            $table->string('nama_layanan', 200);
            $table->text('spesifikasi_draft')->nullable();
            $table->unsignedSmallInteger('masa_uji_hari')->default(30);
            $table->string('spesifikasi_file_path', 255)->nullable();

            // Hasil provisioning (record-keeping, environment sandbox)
            $table->foreignId('splp_service_id')->nullable()->constrained('splp_services')->nullOnDelete();
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

            // Verifikasi & catatan
            $table->boolean('check_administrasi')->default(false);
            $table->boolean('check_spesifikasi')->default(false);
            $table->boolean('check_sumberdaya')->default(false);
            $table->text('catatan_verifikasi')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('consent_true')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('splp_sandbox_requests');
    }
};
