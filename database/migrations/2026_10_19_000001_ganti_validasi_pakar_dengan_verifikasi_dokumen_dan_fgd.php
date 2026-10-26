<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Proposal v4: validasi model tidak lagi memakai panel pakar (Delphi), tetapi dua tahap:
     * (1) verifikasi silang setiap butir pemetaan terhadap dokumen acuan, lalu
     * (2) FGD bersama praktisi DKISP (informan) yang memutuskan butir secara mufakat.
     * Hasil kesepakatan pakar sebelumnya tidak dipakai lagi, sehingga status butir dikembalikan ke draf.
     */
    public function up(): void
    {
        Schema::table('tata_kelola_keamanan_pemetaans', function (Blueprint $table) {
            // null = belum diverifikasi | didukung | rancangan_peneliti | tidak_didukung
            $table->string('verifikasi', 20)->nullable()->after('status');
            // {kode dokumen acuan: rujukan halaman/pasal/tabel}
            $table->json('dokumen_acuan')->nullable()->after('verifikasi');
            $table->text('catatan_verifikasi')->nullable()->after('dokumen_acuan');
            $table->text('alasan_dikeluarkan')->nullable()->after('catatan_revisi');
            $table->renameColumn('putaran_disepakati', 'sesi_disepakati');
        });

        DB::table('tata_kelola_keamanan_pemetaans')->update(['status' => 'draf', 'sesi_disepakati' => null]);

        Schema::create('tata_kelola_keamanan_sesi_fgds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_id')->constrained('tata_kelola_keamanan_evaluasis')->cascadeOnDelete();
            $table->unsignedTinyInteger('nomor');
            $table->date('tanggal')->nullable();
            $table->string('tempat')->nullable();
            // id responden (informan) yang hadir
            $table->json('peserta')->nullable();
            $table->text('peserta_lain')->nullable();
            $table->text('catatan')->nullable();
            // dibuka | ditutup
            $table->string('status', 20)->default('dibuka');
            $table->timestamp('ditutup_at')->nullable();
            $table->timestamps();
            $table->unique(['evaluasi_id', 'nomor'], 'tkk_sesi_fgd_unik');
        });

        // Keputusan forum per butir pada satu sesi (mufakat), bukan suara per peserta.
        Schema::create('tata_kelola_keamanan_hasil_fgds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_fgd_id')->constrained('tata_kelola_keamanan_sesi_fgds')->cascadeOnDelete();
            $table->foreignId('pemetaan_id')->constrained('tata_kelola_keamanan_pemetaans')->cascadeOnDelete();
            // setuju | tidak_setuju
            $table->string('keputusan', 20);
            $table->text('saran')->nullable();
            $table->timestamps();
            $table->unique(['sesi_fgd_id', 'pemetaan_id'], 'tkk_hasil_fgd_unik');
        });

        Schema::dropIfExists('tata_kelola_keamanan_penilaians');
        Schema::dropIfExists('tata_kelola_keamanan_putarans');
        Schema::dropIfExists('tata_kelola_keamanan_pakars');

        $permIds = DB::table('permissions')->where('name', 'Validasi Pakar Tata Kelola Keamanan')->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $permIds)->delete();
        DB::table('permissions')->whereIn('id', $permIds)->delete();

        $roleIds = DB::table('roles')->where('name', 'Pakar Tata Kelola Keamanan')->pluck('id');
        DB::table('permission_role')->whereIn('role_id', $roleIds)->delete();
        DB::table('role_user')->whereIn('role_id', $roleIds)->delete();
        DB::table('roles')->whereIn('id', $roleIds)->delete();

        DB::table('permissions')->where('name', 'Kelola Tata Kelola Keamanan')->update([
            'description' => 'Mengelola evaluasi: responden, design factors, pemetaan COBIT–ISO–regulasi, validasi model (verifikasi dokumen dan FGD), asesmen kapabilitas, kesenjangan, dan laporan',
        ]);
    }

    /** Mengembalikan struktur panel pakar (tanpa data penilaiannya). */
    public function down(): void
    {
        Schema::dropIfExists('tata_kelola_keamanan_hasil_fgds');
        Schema::dropIfExists('tata_kelola_keamanan_sesi_fgds');

        Schema::table('tata_kelola_keamanan_pemetaans', function (Blueprint $table) {
            $table->renameColumn('sesi_disepakati', 'putaran_disepakati');
            $table->dropColumn(['verifikasi', 'dokumen_acuan', 'catatan_verifikasi', 'alasan_dikeluarkan']);
        });

        Schema::create('tata_kelola_keamanan_pakars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_id')->constrained('tata_kelola_keamanan_evaluasis')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('instansi')->nullable();
            $table->string('keahlian')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->unique(['evaluasi_id', 'user_id'], 'tkk_pakar_unik');
        });

        Schema::create('tata_kelola_keamanan_putarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_id')->constrained('tata_kelola_keamanan_evaluasis')->cascadeOnDelete();
            $table->unsignedTinyInteger('nomor');
            $table->string('status', 20)->default('dibuka');
            $table->timestamp('dibuka_at')->nullable();
            $table->timestamp('ditutup_at')->nullable();
            $table->timestamps();
            $table->unique(['evaluasi_id', 'nomor'], 'tkk_putaran_unik');
        });

        Schema::create('tata_kelola_keamanan_penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('putaran_id')->constrained('tata_kelola_keamanan_putarans')->cascadeOnDelete();
            $table->foreignId('pakar_id')->constrained('tata_kelola_keamanan_pakars')->cascadeOnDelete();
            $table->foreignId('pemetaan_id')->constrained('tata_kelola_keamanan_pemetaans')->cascadeOnDelete();
            $table->boolean('setuju');
            $table->text('saran')->nullable();
            $table->timestamps();
            $table->unique(['putaran_id', 'pakar_id', 'pemetaan_id'], 'tkk_penilaian_unik');
        });

        $roleId = DB::table('roles')->insertGetId([
            'name' => 'Pakar Tata Kelola Keamanan',
            'display_name' => 'Pakar Tata Kelola Keamanan',
            'description' => 'Memvalidasi pemetaan COBIT 2019 – ISO/IEC 27001:2022 – regulasi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $permId = DB::table('permissions')->insertGetId([
            'name' => 'Validasi Pakar Tata Kelola Keamanan',
            'display_name' => 'Validasi Pakar Tata Kelola Keamanan Informasi',
            'group' => 'User - Layanan Digital',
            'order' => 99,
            'description' => 'Menilai butir pemetaan COBIT 2019 – ISO/IEC 27001:2022 – regulasi bagi pakar yang didaftarkan pengelola',
            'route_name' => 'tata-kelola-keamanan.pakar.index',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('permission_role')->insert(['role_id' => $roleId, 'permission_id' => $permId, 'created_at' => now(), 'updated_at' => now()]);
    }
};
