<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Inti alur DTSEN: Tahap 2 (pengajuan) s.d. Tahap 4 (pemberian hak akses).
     * Menggabungkan Form 2.1-2.6 (pemohon), Form 3.1-3.4 (verifikasi internal),
     * serta Form 4.1-4.2 (BAST & kesepakatan infrastruktur) dalam satu berkas permohonan.
     */
    public function up(): void
    {
        Schema::create('dtsen_data_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no', 30)->unique()->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->foreignId('dtsen_account_request_id')->nullable()
                ->constrained('dtsen_account_requests')->nullOnDelete();
            $table->foreignId('dtsen_release_id')->nullable()->constrained('dtsen_releases')->nullOnDelete();

            // Form 2.1 - Registrasi pemohon (per pengajuan)
            $table->string('pemohon_nama', 150);
            $table->string('pemohon_nip', 30)->nullable();
            $table->string('pemohon_jabatan', 150)->nullable();
            $table->string('pemohon_telepon', 30);

            // Form 2.2 - Formulir permintaan data (Lampiran II)
            $table->string('nomor_surat', 150)->nullable();
            $table->enum('sifat_surat', ['biasa', 'segera', 'sangat_segera', 'rahasia'])->default('biasa');
            $table->string('jumlah_lampiran', 50)->nullable();
            $table->date('tanggal_surat')->nullable();
            $table->string('nama_program', 255);
            $table->enum('jenis_permintaan', ['bnba', 'pemadanan', 'keduanya'])->default('bnba');
            // Daftar id dtsen_wilayahs terpilih + catatan bebas.
            $table->json('cakupan_wilayah_ids')->nullable();
            $table->text('cakupan_wilayah_catatan')->nullable();
            $table->text('tujuan_penggunaan');
            $table->string('surat_permohonan_path', 255)->nullable();

            // Level hak akses tertinggi yang dimohonkan (Bab IV): 2 kustomisasi, 3 mikro, 4 BNBA.
            $table->unsignedTinyInteger('level_akses')->default(2)->index();

            // Form 2.6 - Kesiapan teknis & keamanan
            $table->enum('metode_akses', ['api', 'excel_terenkripsi', 'vpn'])->nullable();
            $table->string('metode_enkripsi', 255)->nullable();
            $table->text('kapasitas_sdm')->nullable();

            // Form 2.4 - Kerangka Acuan Kerja (wajib level 3 & 4), Lampiran IV
            $table->text('kak_latar_belakang')->nullable();
            $table->json('kak_dasar_hukum')->nullable();
            $table->text('kak_maksud_tujuan')->nullable();
            $table->text('kak_metodologi')->nullable();
            $table->text('kak_keluaran')->nullable();
            $table->string('kak_unit_akses', 255)->nullable();
            $table->date('kak_jangka_mulai')->nullable();
            $table->date('kak_jangka_akhir')->nullable();
            $table->text('kak_infrastruktur_penyimpanan')->nullable();
            $table->json('kak_personel_akses')->nullable();
            $table->json('kak_teknik_pelindungan')->nullable();
            $table->date('kak_retensi_batas_waktu')->nullable();
            $table->text('kak_metode_pemusnahan')->nullable();
            $table->boolean('kak_pernyataan')->default(false);
            $table->string('kak_file_path', 255)->nullable();

            // Siklus hidup permohonan (tracking status lintas tahapan)
            $table->enum('status', [
                'draft', 'diajukan', 'verifikasi_administrasi', 'perlu_perbaikan',
                'verifikasi_substansi', 'klarifikasi', 'ditolak', 'diterima',
                'pemrosesan_qa', 'menunggu_bast', 'data_tersedia', 'selesai', 'kedaluwarsa',
            ])->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verif_admin_at')->nullable();
            $table->timestamp('dikembalikan_at')->nullable();
            $table->timestamp('verif_substansi_at')->nullable();
            $table->timestamp('klarifikasi_at')->nullable();
            $table->timestamp('diterima_at')->nullable();
            $table->timestamp('ditolak_at')->nullable();
            $table->timestamp('pemrosesan_at')->nullable();
            $table->timestamp('bast_at')->nullable();
            $table->timestamp('akses_at')->nullable();
            $table->timestamp('selesai_at')->nullable();

            // Form 3.1 - Verifikasi administrasi (DKISP Bidang Statistik)
            $table->boolean('adm_check_surat')->default(false);
            $table->boolean('adm_check_kak')->default(false);
            $table->boolean('adm_check_dokumen_pendukung')->default(false);
            $table->boolean('adm_check_metode_akses')->default(false);
            $table->boolean('adm_check_enkripsi')->default(false);
            $table->text('adm_catatan')->nullable();
            $table->foreignId('adm_verified_by')->nullable()->constrained('users')->nullOnDelete();

            // Form 3.2 - Verifikasi substansi (Koordinator Forum SDD / Bapperida)
            $table->enum('sub_hasil', ['diterima', 'ditolak', 'klarifikasi'])->nullable();
            $table->text('sub_catatan')->nullable();
            $table->text('sub_alasan_penolakan')->nullable();
            $table->foreignId('sub_verified_by')->nullable()->constrained('users')->nullOnDelete();

            // Form 3.4 - Pemrosesan data & QA (DKISP)
            $table->boolean('qa_check_pemilahan')->default(false);
            $table->boolean('qa_check_agregasi')->default(false);
            $table->boolean('qa_check_mutu')->default(false);
            $table->boolean('qa_check_kesesuaian')->default(false);
            $table->text('qa_catatan')->nullable();
            $table->foreignId('qa_by')->nullable()->constrained('users')->nullOnDelete();

            // Form 4.1 - BAST diunggah pemohon sebelum hak akses diberikan
            $table->string('bast_nomor', 150)->nullable();
            $table->date('bast_tanggal')->nullable();
            $table->string('bast_file_path', 255)->nullable();
            $table->timestamp('bast_uploaded_at')->nullable();
            $table->boolean('bast_verified')->default(false);
            $table->foreignId('bast_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('bast_catatan')->nullable();

            // Form 4.2 - Kesepakatan infrastruktur pengiriman (final, DKISP - OPD)
            $table->enum('infra_final', ['api', 'excel_terenkripsi', 'vpn'])->nullable();
            $table->text('infra_parameter')->nullable();

            // Form 4.5 - Permintaan ulang/pembaruan data
            $table->foreignId('parent_request_id')->nullable()
                ->constrained('dtsen_data_requests')->nullOnDelete();
            $table->boolean('ada_perubahan_signifikan')->default(true);

            $table->boolean('consent_true')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_data_requests');
    }
};
