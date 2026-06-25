<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * V4 — Perubahan / Perpanjangan Konfigurasi Endpoint SPLP.
     */
    public function up(): void
    {
        Schema::create('splp_change_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no', 30)->unique()->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Pemohon
            $table->string('nama', 200);
            $table->string('nip', 30)->nullable();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->string('email_pemohon', 200);
            $table->string('no_hp', 30);

            // Layanan terdaftar yang akan diubah/diperpanjang
            $table->foreignId('splp_service_id')->constrained('splp_services')->cascadeOnDelete();
            $table->enum('kategori', ['perubahan', 'perpanjangan'])->default('perubahan');
            $table->enum('jenis_perubahan', ['minor', 'signifikan'])->default('minor');
            $table->text('detail_perubahan');
            $table->date('perpanjangan_sampai')->nullable();
            $table->text('analisis_dampak')->nullable();
            $table->string('surat_path', 255)->nullable();

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
            $table->boolean('check_dampak')->default(false);
            $table->text('catatan_verifikasi')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('consent_true')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('splp_change_requests');
    }
};
