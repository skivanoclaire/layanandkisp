<?php

namespace Tests\Feature\TataKelolaKeamanan;

use App\Models\Role;
use App\Models\TataKelolaKeamanan\Aktivitas;
use App\Models\TataKelolaKeamanan\Pemetaan;
use App\Models\TataKelolaKeamanan\HasilFgd;
use App\Models\TataKelolaKeamanan\Praktik;
use App\Models\TataKelolaKeamanan\Evaluasi;
use App\Models\TataKelolaKeamanan\LampiranJawaban;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Services\TataKelolaKeamanan\RoleModul;
use Database\Seeders\TataKelolaKeamananSimulasiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Modul Tesis: model integrasi COBIT 2019 dan ISO/IEC 27001:2022 (proposal v4: validasi dokumen + FGD).
 */
class TataKelolaKeamananTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert(['role_id' => Role::where('name', 'Admin')->value('id'), 'user_id' => $user->id]);

        return $user;
    }

    private function evaluasiDenganPemetaan(): array
    {
        $evaluasi = Evaluasi::create(['nama' => 'Uji', 'instansi' => 'DKISP']);
        $praktik = Praktik::create(['evaluasi_id' => $evaluasi->id, 'objektif_kode' => 'APO13', 'kode' => 'APO13.01', 'nama' => 'ISMS']);
        $evaluasi->objektifs()->create(['kode' => 'APO13', 'skor_kepentingan' => 90, 'terkait_keamanan' => true, 'dipilih' => true]);
        $pemetaan = Pemetaan::create([
            'evaluasi_id' => $evaluasi->id, 'praktik_id' => $praktik->id, 'iso_items' => ['K4.3', 'A.5.1'],
            'regulasi_items' => ['PG20-3'], 'status' => Pemetaan::STATUS_DRAF,
        ]);

        return [$evaluasi, $praktik, $pemetaan];
    }

    public function test_migration_memberi_permission_ke_role_admin(): void
    {
        $this->assertTrue(
            DB::table('permission_role')
                ->join('roles', 'roles.id', '=', 'permission_role.role_id')
                ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                ->where('roles.name', 'Admin')->where('permissions.name', 'Kelola Tata Kelola Keamanan')->exists()
        );
    }

    public function test_pengguna_tanpa_permission_ditolak(): void
    {
        $user = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert(['role_id' => Role::where('name', 'User')->value('id'), 'user_id' => $user->id]);

        $this->actingAs($user)->get(route('admin.tata-kelola-keamanan.index'))->assertForbidden();
    }

    public function test_seluruh_halaman_terbuka_dengan_data_simulasi(): void
    {
        $this->seed(TataKelolaKeamananSimulasiSeeder::class);
        $evaluasi = Evaluasi::where('is_simulasi', true)->firstOrFail();
        $admin = $this->admin();

        foreach (['show', 'responden', 'design-factors', 'objektif', 'pemetaan', 'validasi', 'asesmen', 'kontrol', 'rekomendasi', 'laporan'] as $halaman) {
            $this->actingAs($admin)->get(route("admin.tata-kelola-keamanan.$halaman", $evaluasi))->assertOk();
        }

        // Halaman responden simulasi
        $responden = User::where('email', 'responden1.simulasi@example.test')->firstOrFail();
        $this->actingAs($responden)->get(route('tata-kelola-keamanan.responden.index'))->assertOk()->assertSee('Kuesioner aktivitas');
        $this->actingAs($responden)->get(route('tata-kelola-keamanan.responden.design-factors', $evaluasi))->assertOk()->assertSee('DF11');
        $this->actingAs($responden)->get(route('tata-kelola-keamanan.responden.aktivitas', $evaluasi))->assertOk()->assertSee('APO12');
        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.validasi.lembar', [$evaluasi, 'sesi' => 2]))->assertOk()
            ->assertSee('Lembar Validasi Model')->assertSee('DSS05.05');
        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.laporan', $evaluasi))->assertOk()
            ->assertSee('Hasil validasi model')->assertSee('FGD sesi 2');
        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.index'))->assertOk()->assertSee('[SIMULASI]', false);
        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.export', $evaluasi))->assertOk()
            ->assertHeader('content-disposition');

        // Seeder: semua butir disepakati setelah dua sesi FGD
        $this->assertSame(0, $evaluasi->pemetaans()->where('status', Pemetaan::STATUS_DRAF)->count());
        $this->assertSame(2, $evaluasi->sesiFgds()->count());
    }

    public function test_saran_pemilihan_objektif_memakai_skor_minimal_dan_batas_delapan(): void
    {
        $evaluasi = Evaluasi::create(['nama' => 'Uji']);
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.objektif', $evaluasi))->assertOk();
        $this->assertSame(40, $evaluasi->objektifs()->count());

        // 10 objektif keamanan dengan skor >= 50, satu objektif non-keamanan skor tinggi
        $skor = ['APO12' => 95, 'APO13' => 90, 'DSS05' => 100, 'DSS04' => 75, 'EDM03' => 70, 'MEA03' => 60, 'APO14' => 55,
            'BAI10' => 50, 'DSS02' => 52, 'BAI06' => 51, 'APO02' => 99, 'APO10' => 45];
        foreach ($skor as $kode => $s) {
            $evaluasi->objektifs()->where('kode', $kode)->update(['skor_kepentingan' => $s]);
        }

        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.objektif.saran', $evaluasi))->assertRedirect();

        $dipilih = $evaluasi->objektifs()->where('dipilih', true)->orderBy('kode')->pluck('kode')->all();
        $this->assertCount(8, $dipilih);
        $this->assertNotContains('APO02', $dipilih);   // bukan objektif keamanan
        $this->assertNotContains('APO10', $dipilih);   // skor < 50
        $this->assertNotContains('BAI10', $dipilih);   // skor terendah, terpotong batas 8
        $this->assertContains('DSS05', $dipilih);

        // Praktik untuk objektif terpilih otomatis disiapkan dari referensi
        $this->assertTrue(Praktik::where('evaluasi_id', $evaluasi->id)->where('kode', 'APO13.01')->exists());
        // Target otomatis dari skor kepentingan
        $this->assertSame(4, $evaluasi->objektifs()->where('kode', 'DSS04')->first()->target_level);
        $this->assertSame(3, $evaluasi->objektifs()->where('kode', 'EDM03')->first()->target_level);
    }

    public function test_alur_validasi_model_dari_verifikasi_dokumen_sampai_kesepakatan_fgd(): void
    {
        [$evaluasi, $praktik, $pemetaan] = $this->evaluasiDenganPemetaan();
        $kedua = Pemetaan::create(['evaluasi_id' => $evaluasi->id, 'praktik_id' => $praktik->id, 'iso_items' => ['A.8.12'], 'status' => Pemetaan::STATUS_DRAF]);
        $informan = $evaluasi->respondens()->create(['user_id' => $this->akunUser(RoleModul::RESPONDEN)->id, 'jabatan' => 'Kepala Bidang Persandian']);
        $admin = $this->admin();

        // Sesi FGD tidak dapat dibuka sebelum ada butir yang lolos verifikasi dokumen
        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.sesi-fgd.buka', $evaluasi), ['tanggal' => '2026-11-02', 'peserta' => [$informan->id]])
            ->assertSessionHas('error');

        // Verifikasi: butir pertama didukung dua dokumen acuan, butir kedua belum ditemukan dukungannya
        $this->actingAs($admin)->put(route('admin.tata-kelola-keamanan.validasi.verifikasi', [$evaluasi, $pemetaan]), [
            'dokumen' => ['COBIT-RG', 'ISO27002'], 'rujukan' => ['COBIT-RG' => 'hlm. 120', 'ISO27002' => 'Tabel B.1'],
        ])->assertRedirect();
        $this->actingAs($admin)->put(route('admin.tata-kelola-keamanan.validasi.verifikasi', [$evaluasi, $kedua]), [])->assertRedirect();
        $this->assertSame(Pemetaan::VERIFIKASI_DIDUKUNG, $pemetaan->fresh()->verifikasi);
        $this->assertSame(['COBIT-RG' => 'hlm. 120', 'ISO27002' => 'Tabel B.1'], $pemetaan->fresh()->dokumen_acuan);
        $this->assertSame(Pemetaan::VERIFIKASI_TIDAK_DIDUKUNG, $kedua->fresh()->verifikasi);

        // Sesi 1: hanya butir yang lolos verifikasi yang dibahas
        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.sesi-fgd.buka', $evaluasi), [
            'tanggal' => '2026-11-02', 'tempat' => 'Ruang rapat', 'peserta' => [$informan->id],
        ])->assertRedirect();
        $sesi = $evaluasi->sesiAktif();
        $this->assertSame(1, $sesi->nomor);
        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.validasi.lembar', $evaluasi))->assertOk()
            ->assertSee('APO13.01')->assertSee('hlm. 120')->assertDontSee('A.8.12');

        // Pemetaan dan verifikasi terkunci selama sesi dibuka
        $this->actingAs($admin)->put(route('admin.tata-kelola-keamanan.pemetaan.update', [$evaluasi, $kedua]), [
            'praktik_id' => $praktik->id, 'iso_items' => ['A.7.2'],
        ])->assertRedirect();
        $this->assertSame(['A.8.12'], $kedua->fresh()->iso_items);

        // Forum tidak setuju; butir tetap draf untuk diperbaiki
        $this->actingAs($admin)->put(route('admin.tata-kelola-keamanan.sesi-fgd.simpan', [$evaluasi, $sesi]), [
            'tanggal' => '2026-11-02', 'peserta' => [$informan->id], 'catatan' => 'Mufakat',
            'keputusan' => [$pemetaan->id => 'tidak_setuju', $kedua->id => 'setuju'],
            'saran' => [$pemetaan->id => 'Tambahkan klausul 5.2'],
        ])->assertRedirect();
        $this->assertSame(1, HasilFgd::count()); // butir kedua bukan bagian sesi ini
        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.sesi-fgd.tutup', [$evaluasi, $sesi]))->assertRedirect();
        $this->assertSame(Pemetaan::STATUS_DRAF, $pemetaan->fresh()->status);

        // Perbaikan butir membatalkan verifikasinya
        $this->actingAs($admin)->put(route('admin.tata-kelola-keamanan.pemetaan.update', [$evaluasi, $pemetaan]), [
            'praktik_id' => $praktik->id, 'iso_items' => ['K4.3', 'K5.2', 'A.5.1'], 'catatan_revisi' => 'Saran FGD sesi 1',
        ])->assertRedirect();
        $this->assertNull($pemetaan->fresh()->verifikasi);
        $this->actingAs($admin)->put(route('admin.tata-kelola-keamanan.validasi.verifikasi', [$evaluasi, $pemetaan]), ['dokumen' => ['COBIT-RG']]);

        // Butir kedua dipertahankan sebagai rancangan peneliti hanya bila dijelaskan
        $this->actingAs($admin)->put(route('admin.tata-kelola-keamanan.validasi.verifikasi', [$evaluasi, $kedua]), ['pertahankan' => 1])
            ->assertSessionHas('error');
        $this->actingAs($admin)->put(route('admin.tata-kelola-keamanan.validasi.verifikasi', [$evaluasi, $kedua]), [
            'pertahankan' => 1, 'catatan_verifikasi' => 'Keterkaitan dijelaskan dari tujuan praktik',
        ]);
        $this->assertSame(Pemetaan::VERIFIKASI_RANCANGAN_PENELITI, $kedua->fresh()->verifikasi);

        // Sesi 2: keduanya dibahas; satu disepakati, satu tetap tidak disepakati lalu dikeluarkan
        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.sesi-fgd.buka', $evaluasi), ['tanggal' => '2026-11-09', 'peserta_lain' => 'Staf Bidang Aplikasi']);
        $sesi2 = $evaluasi->sesiAktif();
        $this->assertSame(2, $sesi2->nomor);
        $this->actingAs($admin)->put(route('admin.tata-kelola-keamanan.sesi-fgd.simpan', [$evaluasi, $sesi2]), [
            'tanggal' => '2026-11-09', 'peserta_lain' => 'Staf Bidang Aplikasi',
            'keputusan' => [$pemetaan->id => 'setuju', $kedua->id => 'tidak_setuju'],
        ]);
        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.sesi-fgd.tutup', [$evaluasi, $sesi2]));
        $this->assertSame(Pemetaan::STATUS_DISEPAKATI, $pemetaan->fresh()->status);
        $this->assertSame(2, $pemetaan->fresh()->sesi_disepakati);

        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.validasi.keluarkan', [$evaluasi, $kedua]), [])->assertSessionHasErrors('alasan_dikeluarkan');
        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.validasi.keluarkan', [$evaluasi, $kedua]), ['alasan_dikeluarkan' => 'Tidak disepakati FGD sesi 2']);
        $this->assertSame(Pemetaan::STATUS_DIKELUARKAN, $kedua->fresh()->status);

        // Butir yang sudah dibahas dalam FGD tidak dapat dihapus
        $this->actingAs($admin)->delete(route('admin.tata-kelola-keamanan.pemetaan.destroy', [$evaluasi, $kedua]))->assertRedirect();
        $this->assertNotNull($kedua->fresh());

        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.laporan', $evaluasi))->assertOk()
            ->assertSee('Tidak disepakati FGD sesi 2')->assertSee('Staf Bidang Aplikasi');
    }

    public function test_sesi_fgd_wajib_tanggal_dan_peserta_dari_evaluasi_yang_sama(): void
    {
        [$evaluasi, , $pemetaan] = $this->evaluasiDenganPemetaan();
        $pemetaan->update(['verifikasi' => Pemetaan::VERIFIKASI_DIDUKUNG, 'dokumen_acuan' => ['COBIT-RG' => null]]);
        $evaluasiLain = Evaluasi::create(['nama' => 'Lain']);
        $orangLain = $evaluasiLain->respondens()->create(['user_id' => $this->akunUser(RoleModul::RESPONDEN)->id, 'jabatan' => 'X']);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.sesi-fgd.buka', $evaluasi), [])
            ->assertSessionHasErrors(['tanggal', 'peserta']);
        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.sesi-fgd.buka', $evaluasi), ['tanggal' => '2026-11-02', 'peserta' => [$orangLain->id]])
            ->assertSessionHasErrors('peserta.0');
        $this->assertSame(0, $evaluasi->sesiFgds()->count());

        // Sesi milik evaluasi lain tidak dapat diubah lewat evaluasi ini
        $sesiLain = $evaluasiLain->sesiFgds()->create(['nomor' => 1, 'status' => 'dibuka']);
        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.sesi-fgd.tutup', [$evaluasi, $sesiLain]))->assertNotFound();
    }

    public function test_migration_menghapus_role_dan_permission_pakar(): void
    {
        $this->assertFalse(DB::table('roles')->where('name', 'Pakar Tata Kelola Keamanan')->exists());
        $this->assertFalse(DB::table('permissions')->where('name', 'Validasi Pakar Tata Kelola Keamanan')->exists());
    }

    public function test_asesmen_aktivitas_dan_impor(): void
    {
        [$evaluasi, $praktik] = $this->evaluasiDenganPemetaan();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.aktivitas.impor', $evaluasi), [
            'teks' => "APO13.01 | 2 | Aktivitas satu | Dokumen A\nAPO13.01 | 2 | Aktivitas dua\nXXX | 2 | salah\nAPO13.01 | 3 | Aktivitas tiga",
        ])->assertSessionHas('warning');
        $this->assertSame(3, Aktivitas::count());

        foreach (Aktivitas::where('level', 2)->get() as $a) {
            $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.aktivitas.nilai', [$evaluasi, $a]), [
                'terpenuhi' => '1', 'bukti_ditemukan' => 'ada', 'sumber_bukti' => ['dokumen'],
            ])->assertRedirect();
        }

        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.asesmen', [$evaluasi, 'objektif' => 'APO13']))
            ->assertOk()->assertSee('Fully achieved');

        $ringkasan = app(\App\Services\TataKelolaKeamanan\Kapabilitas::class)->ringkasanEvaluasi($evaluasi)->first();
        $this->assertSame(2, $ringkasan['hasil']['level']);
        $this->assertSame(4, $ringkasan['target']);
        $this->assertSame(2, $ringkasan['gap']);
    }

    public function test_aktivitas_evaluasi_lain_tidak_dapat_diubah(): void
    {
        [$evaluasi, $praktik] = $this->evaluasiDenganPemetaan();
        $lain = Evaluasi::create(['nama' => 'Lain']);
        $a = Aktivitas::create(['praktik_id' => $praktik->id, 'level' => 2, 'uraian' => 'x']);

        $this->actingAs($this->admin())->post(route('admin.tata-kelola-keamanan.aktivitas.nilai', [$lain, $a]), ['terpenuhi' => '1'])
            ->assertNotFound();
    }

    public function test_status_kontrol_hanya_untuk_kode_annex_a(): void
    {
        [$evaluasi] = $this->evaluasiDenganPemetaan();
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.kontrol', $evaluasi))->assertOk()->assertSee('A.5.1');
        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.kontrol.simpan', $evaluasi), [
            'status' => ['A.5.1' => 'sebagian', 'Z.9.9' => 'diterapkan'],
            'bukti' => ['A.5.1' => 'Draf kebijakan'],
        ])->assertRedirect();

        $this->assertSame(1, $evaluasi->kontrols()->count());
        $this->assertSame('sebagian', $evaluasi->kontrols()->first()->status);
    }

    private function akunUser(?string $roleModul = null): User
    {
        $user = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert(['role_id' => Role::where('name', 'User')->value('id'), 'user_id' => $user->id]);
        if ($roleModul) {
            app(RoleModul::class)->pasang($user, $roleModul);
        }

        return $user->fresh();
    }

    private function punyaRole(User $user, string $role): bool
    {
        return DB::table('role_user')->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)->where('roles.name', $role)->exists();
    }

    public function test_migration_membuat_role_khusus_dengan_permission_masing_masing(): void
    {
        $harapan = [
            RoleModul::PENGELOLA => 'Kelola Tata Kelola Keamanan',
            RoleModul::RESPONDEN => 'Isi Kuesioner Tata Kelola Keamanan',
        ];
        foreach ($harapan as $role => $permission) {
            $this->assertTrue(
                DB::table('permission_role')
                    ->join('roles', 'roles.id', '=', 'permission_role.role_id')
                    ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                    ->where('roles.name', $role)->where('permissions.name', $permission)->exists(),
                "$permission belum diberikan ke role $role"
            );
        }
        // Role User biasa tidak otomatis mendapat akses kuesioner
        $this->assertFalse(
            DB::table('permission_role')
                ->join('roles', 'roles.id', '=', 'permission_role.role_id')
                ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                ->where('roles.name', 'User')->where('permissions.name', 'Isi Kuesioner Tata Kelola Keamanan')->exists()
        );
    }

    public function test_role_pengelola_dapat_membuka_modul(): void
    {
        $user = $this->akunUser(RoleModul::PENGELOLA);
        $this->actingAs($user)->get(route('admin.tata-kelola-keamanan.index'))->assertOk()->assertSee('Tata Kelola Keamanan Informasi');
    }

    public function test_akun_yang_bukan_responden_tidak_dapat_mengisi(): void
    {
        [$evaluasi] = $this->evaluasiDenganPemetaan();
        $this->actingAs($this->akunUser())->get(route('tata-kelola-keamanan.responden.index'))->assertForbidden();
        $user = $this->akunUser(RoleModul::RESPONDEN);

        $this->actingAs($user)->get(route('tata-kelola-keamanan.responden.index'))->assertOk()->assertSee('belum didaftarkan');
        $this->actingAs($user)->get(route('tata-kelola-keamanan.responden.design-factors', $evaluasi))->assertForbidden();
        $this->actingAs($user)->post(route('tata-kelola-keamanan.responden.design-factors.simpan', $evaluasi), ['nilai' => ['DF5' => [50, 50]]])->assertForbidden();
    }

    public function test_admin_mendaftarkan_responden_dan_responden_mengisi_design_factors(): void
    {
        [$evaluasi] = $this->evaluasiDenganPemetaan();
        $admin = $this->admin();
        $user = $this->akunUser();

        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.responden', [$evaluasi, 'q' => mb_substr($user->name, 0, 4)]))
            ->assertOk()->assertSee($user->email);
        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.responden.store', $evaluasi), [
            'user_id' => $user->id, 'jabatan' => 'Kepala Dinas', 'isi_df' => '1',
        ])->assertRedirect();
        $this->assertTrue($this->punyaRole($user, RoleModul::RESPONDEN));
        $user = $user->fresh();

        $this->actingAs($user)->get(route('tata-kelola-keamanan.responden.index'))->assertOk()->assertSee('Kuesioner design factors');
        $this->actingAs($user)->post(route('tata-kelola-keamanan.responden.design-factors.simpan', $evaluasi), [
            'nilai' => ['DF5' => [70, 30], 'DF7' => [1, 4, 2, 3]],
        ])->assertRedirect(route('tata-kelola-keamanan.responden.design-factors', $evaluasi));

        $df = $evaluasi->designFactors()->where('df_kode', 'DF5')->first();
        $this->assertNotNull($df->responden_id);
        $this->assertSame([70, 30], $df->nilai);
        $this->assertStringContainsString('Kepala Dinas', $df->responden);

        // Muncul di halaman admin sebagai isian akun dan tidak dapat diubah admin
        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.design-factors', [$evaluasi, 'responden' => $df->responden]))
            ->assertOk()->assertSee('(akun)', false)->assertSee('hanya dapat dibaca');
        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.design-factors.store', $evaluasi), [
            'responden' => $df->responden, 'nilai' => ['DF5' => [10, 90]],
        ])->assertSessionHas('error');
        $this->assertSame([70, 30], $df->fresh()->nilai);

        // Belum ditugaskan aktivitas
        $this->actingAs($user)->get(route('tata-kelola-keamanan.responden.aktivitas', $evaluasi))->assertForbidden();
    }

    public function test_informan_hanya_menjawab_aktivitas_objektif_yang_ditugaskan(): void
    {
        [$evaluasi, $praktik] = $this->evaluasiDenganPemetaan();
        $evaluasi->objektifs()->create(['kode' => 'DSS05', 'skor_kepentingan' => 90, 'terkait_keamanan' => true, 'dipilih' => true]);
        $praktikLain = Praktik::create(['evaluasi_id' => $evaluasi->id, 'objektif_kode' => 'DSS05', 'kode' => 'DSS05.01', 'nama' => 'Malware']);
        $milik = Aktivitas::create(['praktik_id' => $praktik->id, 'level' => 2, 'uraian' => 'Aktivitas APO13 untuk informan']);
        $lain = Aktivitas::create(['praktik_id' => $praktikLain->id, 'level' => 2, 'uraian' => 'Aktivitas DSS05 rahasia']);
        $user = $this->akunUser(RoleModul::RESPONDEN);
        $responden = $evaluasi->respondens()->create(['user_id' => $user->id, 'jabatan' => 'Kabid Persandian', 'objektif' => ['APO13']]);

        $this->actingAs($user)->get(route('tata-kelola-keamanan.responden.aktivitas', $evaluasi))
            ->assertOk()->assertSee('Aktivitas APO13 untuk informan')->assertDontSee('Aktivitas DSS05 rahasia');
        $this->actingAs($user)->get(route('tata-kelola-keamanan.responden.aktivitas', [$evaluasi, 'objektif' => 'DSS05']))->assertNotFound();

        $this->actingAs($user)->post(route('tata-kelola-keamanan.responden.aktivitas.simpan', $evaluasi), [
            'objektif' => 'APO13',
            'jawaban' => [$milik->id => 'ya', $lain->id => 'ya'],
            'keterangan' => [$milik->id => 'SK kebijakan 2025'],
        ])->assertRedirect();

        $this->assertSame(1, $responden->jawabans()->count());
        $this->assertSame('ya', $responden->jawabans()->first()->jawaban);

        // Jawaban tampil di asesmen admin dan tidak mengubah keputusan pengelola
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.asesmen', [$evaluasi, 'objektif' => 'APO13']))
            ->assertOk()->assertSee('Jawaban informan: 1 ya')->assertSee('SK kebijakan 2025');
        $this->assertNull($milik->fresh()->terpenuhi);

        // Aktivitas yang sudah dijawab tidak dapat dihapus; responden yang sudah menjawab tidak dapat dihapus
        $this->actingAs($admin)->delete(route('admin.tata-kelola-keamanan.aktivitas.destroy', [$evaluasi, $milik]))->assertSessionHas('error');
        $this->assertNotNull($milik->fresh());
        $this->actingAs($admin)->delete(route('admin.tata-kelola-keamanan.responden.destroy', [$evaluasi, $responden]))->assertSessionHas('error');
    }

    private function informanDenganAktivitas(): array
    {
        [$evaluasi, $praktik] = $this->evaluasiDenganPemetaan();
        $aktivitas = Aktivitas::create(['praktik_id' => $praktik->id, 'level' => 2, 'uraian' => 'Kebijakan keamanan disahkan']);
        $user = $this->akunUser(RoleModul::RESPONDEN);
        $responden = $evaluasi->respondens()->create(['user_id' => $user->id, 'jabatan' => 'Kabid Persandian', 'objektif' => ['APO13']]);

        return [$evaluasi, $aktivitas, $user, $responden];
    }

    public function test_informan_mengunggah_berkas_bukti_yang_dapat_dibuka_pengelola(): void
    {
        Storage::fake('local');
        [$evaluasi, $aktivitas, $user, $responden] = $this->informanDenganAktivitas();

        $this->actingAs($user)->post(route('tata-kelola-keamanan.responden.aktivitas.simpan', $evaluasi), [
            'objektif' => 'APO13',
            'jawaban' => [$aktivitas->id => 'ya'],
            'lampiran' => [$aktivitas->id => [UploadedFile::fake()->create('SK-kebijakan.pdf', 120, 'application/pdf')]],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $lampiran = LampiranJawaban::firstOrFail();
        $this->assertSame('SK-kebijakan.pdf', $lampiran->nama_asli);
        $this->assertStringStartsWith("tata-kelola-keamanan/{$evaluasi->id}/jawaban/{$responden->id}/", $lampiran->path);
        Storage::disk('local')->assertExists($lampiran->path);

        $this->actingAs($user)->get(route('tata-kelola-keamanan.responden.aktivitas', $evaluasi))->assertOk()->assertSee('SK-kebijakan.pdf');
        $this->actingAs($user)->get(route('tata-kelola-keamanan.responden.lampiran.unduh', [$evaluasi, $lampiran]))->assertOk();

        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.asesmen', [$evaluasi, 'objektif' => 'APO13']))
            ->assertOk()->assertSee('SK-kebijakan.pdf')->assertSee('1 berkas');
        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.lampiran-informan', [$evaluasi, $lampiran]))->assertOk();

        // Informan lain (responden pada evaluasi yang sama) tidak dapat membuka berkas orang lain
        $lain = $this->akunUser(RoleModul::RESPONDEN);
        $evaluasi->respondens()->create(['user_id' => $lain->id, 'jabatan' => 'Staf', 'objektif' => ['APO13']]);
        $this->actingAs($lain)->get(route('tata-kelola-keamanan.responden.lampiran.unduh', [$evaluasi, $lampiran]))->assertNotFound();
        // Pengguna tanpa role responden ditolak
        $this->actingAs($this->akunUser())->get(route('tata-kelola-keamanan.responden.lampiran.unduh', [$evaluasi, $lampiran]))->assertForbidden();
        // Lampiran tidak dapat diakses lewat evaluasi lain
        $evaluasiLain = Evaluasi::create(['nama' => 'Lain']);
        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.lampiran-informan', [$evaluasiLain, $lampiran]))->assertNotFound();

        // Pemilik menghapus berkasnya; berkas fisik ikut terhapus
        $this->actingAs($user)->delete(route('tata-kelola-keamanan.responden.lampiran.hapus', [$evaluasi, $lampiran]))->assertRedirect();
        $this->assertSame(0, LampiranJawaban::count());
        Storage::disk('local')->assertMissing($lampiran->path);
    }

    public function test_unggahan_informan_dibatasi_tipe_jumlah_dan_harus_disertai_jawaban(): void
    {
        Storage::fake('local');
        [$evaluasi, $aktivitas, $user] = $this->informanDenganAktivitas();
        $simpan = fn (array $data) => $this->actingAs($user)->post(route('tata-kelola-keamanan.responden.aktivitas.simpan', $evaluasi), $data + ['objektif' => 'APO13']);

        $simpan(['jawaban' => [$aktivitas->id => 'ya'], 'lampiran' => [$aktivitas->id => [UploadedFile::fake()->create('skrip.exe', 10)]]])
            ->assertSessionHasErrors();
        $simpan(['jawaban' => [], 'lampiran' => [$aktivitas->id => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')]]])
            ->assertSessionHasErrors('lampiran');
        $empat = array_map(fn ($i) => UploadedFile::fake()->create("b$i.pdf", 10, 'application/pdf'), range(1, 4));
        $simpan(['jawaban' => [$aktivitas->id => 'ya'], 'lampiran' => [$aktivitas->id => $empat]])->assertSessionHasErrors();
        $this->assertSame(0, LampiranJawaban::count());

        // Tiga berkas boleh, berkas keempat pada pengiriman berikutnya ditolak
        $tiga = array_map(fn ($i) => UploadedFile::fake()->create("c$i.pdf", 10, 'application/pdf'), range(1, 3));
        $simpan(['jawaban' => [$aktivitas->id => 'ya'], 'lampiran' => [$aktivitas->id => $tiga]])->assertSessionHasNoErrors();
        $simpan(['jawaban' => [$aktivitas->id => 'ya'], 'lampiran' => [$aktivitas->id => [UploadedFile::fake()->create('d.pdf', 10, 'application/pdf')]]])
            ->assertSessionHasErrors('lampiran');
        $this->assertSame(3, LampiranJawaban::count());

        // Menghapus evaluasi ikut menghapus folder berkasnya
        $evaluasi->delete();
        $this->assertSame([], Storage::disk('local')->allFiles("tata-kelola-keamanan/{$evaluasi->id}"));
    }

    public function test_menutup_sesi_tanpa_keputusan_membiarkan_butir_draf(): void
    {
        [$evaluasi, , $pemetaan] = $this->evaluasiDenganPemetaan();
        $pemetaan->update(['verifikasi' => Pemetaan::VERIFIKASI_DIDUKUNG, 'dokumen_acuan' => ['ISO27002' => null]]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.sesi-fgd.buka', $evaluasi), ['tanggal' => '2026-11-02', 'peserta_lain' => 'Kepala Seksi'])
            ->assertSessionHasNoErrors();
        $sesi = $evaluasi->sesiAktif();
        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.sesi-fgd.tutup', [$evaluasi, $sesi]))->assertSessionHas('success');

        $this->assertSame(Pemetaan::STATUS_DRAF, $pemetaan->fresh()->status);
        $this->assertSame('ditutup', $sesi->fresh()->status);
        // Sesi yang sudah ditutup tidak dapat diubah lagi
        $this->actingAs($admin)->put(route('admin.tata-kelola-keamanan.sesi-fgd.simpan', [$evaluasi, $sesi]), [
            'tanggal' => '2026-11-02', 'peserta_lain' => 'x', 'keputusan' => [$pemetaan->id => 'setuju'],
        ])->assertForbidden();
    }

    public function test_panduan_dan_petunjuk_tampil(): void
    {
        [$evaluasi] = $this->evaluasiDenganPemetaan();
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.tata-kelola-keamanan.panduan'))->assertOk()->assertSee('Panduan penggunaan modul');
        foreach (['responden', 'design-factors', 'objektif', 'pemetaan', 'validasi', 'asesmen', 'kontrol', 'rekomendasi'] as $halaman) {
            $this->actingAs($admin)->get(route("admin.tata-kelola-keamanan.$halaman", $evaluasi))->assertOk()->assertSee('Petunjuk:');
        }
    }

    public function test_design_factor_persen_harus_berjumlah_seratus(): void
    {
        $evaluasi = Evaluasi::create(['nama' => 'Uji']);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.design-factors.store', $evaluasi), [
            'responden' => '', 'nilai' => ['DF5' => [60, 30]],
        ])->assertSessionHasErrors('nilai.DF5');

        $this->actingAs($admin)->post(route('admin.tata-kelola-keamanan.design-factors.store', $evaluasi), [
            'responden' => 'Kepala Dinas', 'nilai' => ['DF5' => [60, 40], 'DF1' => [3, 4, 9, 5]],
        ])->assertRedirect();

        $df1 = $evaluasi->designFactors()->where('df_kode', 'DF1')->where('responden', 'Kepala Dinas')->first();
        $this->assertSame([3, 4, null, 5], $df1->nilai); // 9 di luar skala 1–5 diabaikan
    }
}
