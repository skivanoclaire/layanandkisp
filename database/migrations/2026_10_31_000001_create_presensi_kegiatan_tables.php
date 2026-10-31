<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Konfirmasi kehadiran & presensi kegiatan (workshop, rapat, sosialisasi).
 * Halaman publik dibuka/ditutup admin lewat Master Data > Presensi Kegiatan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('nomor_surat')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->string('waktu', 100)->nullable();
            $table->string('tempat');
            $table->string('penyelenggara')->nullable();
            $table->string('kontak')->nullable();
            // Dikirim via WA (Fonnte Aptika) ke peserta yang memilih hadir online
            $table->string('tautan_online', 500)->nullable();
            $table->text('keterangan_online')->nullable();
            $table->unsignedSmallInteger('kuota_per_instansi')->nullable();
            $table->boolean('konfirmasi_aktif')->default(false);
            $table->boolean('presensi_aktif')->default(false);
            $table->boolean('wajib_tanda_tangan')->default(true);
            // Penanda tangan daftar hadir (untuk SPJ)
            $table->string('pj_jabatan')->nullable();
            $table->string('pj_nama')->nullable();
            $table->string('pj_nip', 30)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('presensi_kegiatan_pesertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presensi_kegiatan_id')->constrained('presensi_kegiatans')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama');
            $table->string('nip', 30)->nullable();
            $table->string('jabatan')->nullable();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->string('instansi');
            $table->string('no_hp', 20);
            $table->string('email')->nullable();
            // hadir = di lokasi; online = khusus unit kerja di luar Tanjung Selor
            $table->enum('konfirmasi', ['hadir', 'online'])->nullable();
            $table->timestamp('konfirmasi_at')->nullable();
            $table->string('catatan', 500)->nullable();
            $table->timestamp('hadir_at')->nullable();
            $table->longText('tanda_tangan')->nullable();
            $table->timestamp('wa_online_terkirim_at')->nullable();
            $table->enum('sumber', ['manual', 'akun', 'admin'])->default('manual');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['presensi_kegiatan_id', 'nip']);
            $table->index(['presensi_kegiatan_id', 'no_hp']);
        });

        DB::table('presensi_kegiatans')->insert([
            'slug' => 'workshop-tik-2026',
            'judul' => 'Workshop Aplikasi Portal Layanan TIK, Layanan Publik dan SSO Provinsi Kalimantan Utara',
            'deskripsi' => 'Peserta: staf TIK paling banyak 1 (satu) orang per perangkat daerah, membawa laptop dan data aset aplikasi TIK.',
            'tanggal_mulai' => '2026-11-04',
            'waktu' => '09.00 WITA – Selesai',
            'tempat' => 'Aula Gedung Gadis 1, Jl. Rambutan',
            'penyelenggara' => 'Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Kalimantan Utara',
            'kontak' => 'Fimanisa Arianingrum, A.Md (0853 9331 4138)',
            'kuota_per_instansi' => 1,
            'konfirmasi_aktif' => false,
            'presensi_aktif' => false,
            'wajib_tanda_tangan' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_kegiatan_pesertas');
        Schema::dropIfExists('presensi_kegiatans');
    }
};
