<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tahap 1 — Pembuatan Akun Layanan DTSEN.
     * Form 1.1 (diisi OPD) + Form 1.2 (verifikasi Admin DKISP), Lampiran I Juknis.
     */
    public function up(): void
    {
        Schema::create('dtsen_account_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no', 30)->unique()->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();

            // Data surat permohonan akun
            $table->string('nomor_surat', 150);
            $table->enum('sifat_surat', ['biasa', 'segera', 'sangat_segera', 'rahasia'])->default('biasa');
            $table->string('jumlah_lampiran', 50)->nullable();
            $table->date('tanggal_surat');
            $table->string('surat_path', 255)->nullable();

            // Narahubung teknis OPD
            $table->string('narahubung_nama', 150);
            $table->string('narahubung_kontak', 30);
            $table->string('narahubung_email', 200);

            // Siklus hidup permohonan akun
            $table->enum('status', ['draft', 'diajukan', 'disetujui', 'dikembalikan', 'ditolak'])
                ->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            // Checklist verifikasi (Form 1.2)
            $table->boolean('check_surat_lengkap')->default(false);
            $table->boolean('check_ttd_kepala_opd')->default(false);
            $table->boolean('check_data_personel')->default(false);
            $table->text('catatan_perbaikan')->nullable();

            // Status keaktifan akun layanan DTSEN (auto-nonaktif 30 hari kalender tanpa pemakaian)
            $table->boolean('is_active')->default(false)->index();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('deactivation_warned_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();

            $table->boolean('consent_true')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_account_requests');
    }
};
