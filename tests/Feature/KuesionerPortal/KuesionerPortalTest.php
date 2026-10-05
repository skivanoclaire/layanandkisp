<?php

namespace Tests\Feature\KuesionerPortal;

use App\Models\KuesionerPortalPeriode;
use App\Models\KuesionerPortalResponse;
use App\Models\UnitKerja;
use App\Models\User;
use App\Services\KuesionerPortal\Analisis;
use App\Services\KuesionerPortal\Instrumen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Modul Kuesioner Kualitas Layanan Portal (E-GovQual + IPA + Kano).
 * Terpisah dari Survei Kepuasan Layanan.
 */
class KuesionerPortalTest extends TestCase
{
    use RefreshDatabase;

    private KuesionerPortalPeriode $periode;
    private UnitKerja $unitKerja;

    protected function setUp(): void
    {
        parent::setUp();

        $this->periode = KuesionerPortalPeriode::create(['nama' => 'Uji', 'is_active' => true, 'dibuka_at' => now()]);
        $this->unitKerja = UnitKerja::factory()->create();
    }

    public function test_migration_memberi_permission_ke_role_user_dan_admin(): void
    {
        foreach (['User' => 'Akses Kuesioner Portal', 'Admin' => 'Kelola Kuesioner Portal'] as $role => $permission) {
            $this->assertTrue(
                DB::table('permission_role')
                    ->join('roles', 'roles.id', '=', 'permission_role.role_id')
                    ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                    ->where('roles.name', $role)->where('permissions.name', $permission)->exists(),
                "$permission belum diberikan ke role $role"
            );
        }
    }

    public function test_akun_yang_belum_pernah_mengajukan_permohonan_tidak_dapat_mengisi(): void
    {
        $user = $this->responden(pernahMengajukan: false);

        $this->actingAs($user)->get(route('kuesioner-portal.index'))
            ->assertOk()
            ->assertSee('belum memenuhi kriteria');

        $this->actingAs($user)->post(route('kuesioner-portal.persetujuan'), ['setuju' => 'ya'])
            ->assertForbidden();
    }

    public function test_permohonan_yang_dikecualikan_config_tidak_dihitung(): void
    {
        // Aturan 'kecuali' diterapkan lewat config; uji dengan tabel requests agar tidak
        // bergantung pada banyaknya kolom wajib di rekomendasi_aplikasi_forms.
        config(['kuesioner_portal.tabel_permohonan' => [
            ['tabel' => 'requests', 'layanan' => 'lainnya', 'kecuali' => ['status' => 'Ditolak']],
        ]]);
        $user = $this->responden(pernahMengajukan: false);
        DB::table('requests')->insert(['user_id' => $user->id, 'service' => 'uji', 'status' => 'Ditolak', 'created_at' => now(), 'updated_at' => now()]);

        $this->assertFalse(app(\App\Services\KuesionerPortal\Kelayakan::class)->memenuhi($user));

        DB::table('requests')->insert(['user_id' => $user->id, 'service' => 'uji', 'status' => 'Selesai', 'created_at' => now(), 'updated_at' => now()]);
        $this->assertTrue(app(\App\Services\KuesionerPortal\Kelayakan::class)->memenuhi($user));
    }

    public function test_responden_mengisi_kuesioner_sampai_selesai(): void
    {
        $user = $this->responden();

        $this->actingAs($user)->post(route('kuesioner-portal.persetujuan'), ['setuju' => 'ya'])
            ->assertRedirect(route('kuesioner-portal.isi'));

        $this->actingAs($user)->get(route('kuesioner-portal.isi'))
            ->assertOk()
            ->assertSee('Bagian D. Reaksi terhadap Kondisi Layanan')
            ->assertSee('Bagaimana perasaan Anda apabila struktur menu portal membingungkan?');

        $this->actingAs($user)->post(route('kuesioner-portal.simpan'), $this->payload(hubungiPetugas: false))
            ->assertRedirect(route('kuesioner-portal.lihat'))
            ->assertSessionHasNoErrors();

        $response = KuesionerPortalResponse::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(KuesionerPortalResponse::STATUS_SELESAI, $response->status);
        $this->assertNotNull($response->durasi_detik);
        $this->assertMatchesRegularExpression('/^R-[A-Z0-9]{6}$/', $response->kode_responden);
        $this->assertSame(24, $response->answers()->count());

        // Kinerja atribut petugas tidak disimpan bila responden belum pernah menghubungi petugas
        $this->assertNull($response->answers()->where('kode', 'CS1')->value('kinerja'));
        $this->assertSame(4, $response->answers()->where('kode', 'CS1')->value('kepentingan'));
        $this->assertSame(4, $response->answers()->where('kode', 'EF1')->value('kinerja'));
        $this->assertSame(5, $response->answers()->where('kode', 'KPS1')->value('kinerja'));

        $this->actingAs($user)->get(route('kuesioner-portal.lihat'))->assertOk()->assertSee($response->kode_responden);
    }

    public function test_satu_akun_hanya_mengisi_satu_kali(): void
    {
        $user = $this->responden();
        $this->actingAs($user)->post(route('kuesioner-portal.persetujuan'), ['setuju' => 'ya']);
        $this->actingAs($user)->post(route('kuesioner-portal.simpan'), $this->payload());

        $this->actingAs($user)->post(route('kuesioner-portal.simpan'), $this->payload())
            ->assertRedirect(route('kuesioner-portal.index'))
            ->assertSessionHas('warning');
        $this->actingAs($user)->post(route('kuesioner-portal.persetujuan'), ['setuju' => 'ya'])
            ->assertRedirect(route('kuesioner-portal.lihat'));

        $this->assertSame(1, KuesionerPortalResponse::where('user_id', $user->id)->count());
    }

    public function test_kinerja_petugas_wajib_bila_pernah_menghubungi_petugas(): void
    {
        $user = $this->responden();
        $this->actingAs($user)->post(route('kuesioner-portal.persetujuan'), ['setuju' => 'ya']);

        $payload = $this->payload(hubungiPetugas: true);
        unset($payload['kinerja']['CS2']);

        $this->actingAs($user)->post(route('kuesioner-portal.simpan'), $payload)
            ->assertSessionHasErrors('kinerja.CS2');
    }

    public function test_menolak_persetujuan_tercatat_dan_dapat_berubah_pikiran(): void
    {
        $user = $this->responden();

        $this->actingAs($user)->post(route('kuesioner-portal.persetujuan'), ['setuju' => 'tidak'])
            ->assertRedirect(route('kuesioner-portal.index'));
        $this->assertSame(KuesionerPortalResponse::STATUS_MENOLAK, KuesionerPortalResponse::where('user_id', $user->id)->value('status'));

        $this->actingAs($user)->get(route('kuesioner-portal.isi'))->assertRedirect(route('kuesioner-portal.index'));

        $this->actingAs($user)->post(route('kuesioner-portal.persetujuan'), ['setuju' => 'ya'])
            ->assertRedirect(route('kuesioner-portal.isi'));
        $this->assertSame(KuesionerPortalResponse::STATUS_PERSETUJU, KuesionerPortalResponse::where('user_id', $user->id)->value('status'));
    }

    public function test_pra_pemrosesan_mengeluarkan_straight_lining_durasi_singkat_dan_questionable(): void
    {
        $normal = $this->responsSelesai(fn ($kode) => [4, 3, 1, 5]);
        $seragam = $this->responsSelesai(fn ($kode) => [3, 3, 1, 5], kepuasan: 3);
        $kilat = $this->responsSelesai(fn ($kode) => [4, 3, 1, 5], durasi: 60);
        // Seluruh pasangan Kano (1,1) = Q
        $asal = $this->responsSelesai(fn ($kode) => [4, 3, 1, 1]);

        $eksklusi = app(Analisis::class)->eksklusi(
            KuesionerPortalResponse::with('answers')->whereIn('id', [$normal->id, $seragam->id, $kilat->id, $asal->id])->get()
        );

        $this->assertArrayNotHasKey($normal->kode_responden, $eksklusi['semua']);
        $this->assertArrayNotHasKey($normal->kode_responden, $eksklusi['kano']);
        $this->assertStringContainsString('straight-lining', implode(' ', $eksklusi['semua'][$seragam->kode_responden]));
        $this->assertStringContainsString('Durasi', implode(' ', $eksklusi['semua'][$kilat->kode_responden]));
        $this->assertArrayNotHasKey($asal->kode_responden, $eksklusi['semua']);
        $this->assertArrayHasKey($asal->kode_responden, $eksklusi['kano']);
    }

    public function test_analisis_memadukan_ipa_dan_kano(): void
    {
        // EF1: kepentingan tinggi, kinerja rendah, must-be (F=2, D=5) → Kuadran I, prioritas 1
        // Atribut lain: kepentingan 4, kinerja 4, one-dimensional (F=1, D=5)
        foreach ([[5, 2], [5, 1], [4, 2], [5, 2], [5, 1]] as $i => [$imp, $perf]) {
            $this->responsSelesai(fn ($kode) => $kode === 'EF1' ? [$imp, $perf, 2, 5] : [4, 4, 1, 5], kepuasan: 4 - ($i % 2));
        }

        $hasil = app(Analisis::class)->jalankan($this->periode);

        $this->assertSame(5, $hasil['ringkasan']['dianalisis']);
        $ef1 = $hasil['atribut']['EF1'];
        $this->assertEqualsWithDelta(4.8, $ef1['kepentingan'], 1e-9);
        $this->assertEqualsWithDelta(1.6, $ef1['kinerja'], 1e-9);
        $this->assertEqualsWithDelta(-3.2, $ef1['gap'], 1e-9);
        $this->assertEqualsWithDelta(8 / 24 * 100, $ef1['tk'], 1e-9);
        $this->assertSame('I', $ef1['kuadran']);
        $this->assertSame('M', $ef1['kano']['kategori']);
        $this->assertSame(1, $ef1['prioritas']);
        $this->assertSame('EF1', $hasil['urutan_prioritas'][0]);

        $this->assertSame('O', $hasil['atribut']['TR1']['kano']['kategori']);
        $this->assertEqualsWithDelta(1.0, $hasil['atribut']['TR1']['kano']['better'], 1e-9);
        $this->assertEqualsWithDelta(-1.0, $hasil['atribut']['TR1']['kano']['worse'], 1e-9);
    }

    public function test_halaman_admin_dapat_dibuka_dan_tidak_menampilkan_identitas_responden(): void
    {
        $admin = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert(['role_id' => DB::table('roles')->where('name', 'Admin')->value('id'), 'user_id' => $admin->id]);

        $respons = $this->responsSelesai(fn ($kode) => [4, 3, 2, 5]);
        $nama = $respons->user->name;

        $this->actingAs($admin)->get(route('admin.kuesioner-portal.index'))->assertOk()->assertSee('Periode pengumpulan data');
        $this->actingAs($admin)->get(route('admin.kuesioner-portal.responses', $this->periode))
            ->assertOk()->assertSee($respons->kode_responden)->assertDontSee($nama);
        $this->actingAs($admin)->get(route('admin.kuesioner-portal.show', $respons))
            ->assertOk()->assertDontSee($nama);
        $this->actingAs($admin)->get(route('admin.kuesioner-portal.analisis', $this->periode))
            ->assertOk()->assertSee('Matriks prioritas IPA-Kano')
            ->assertSee('Koefisien better (0 sampai 1)')
            ->assertSee('Kepentingan tinggi, kinerja rendah.');
        $this->actingAs($admin)->get(route('admin.kuesioner-portal.pratinjau'))
            ->assertOk()->assertSee('Mode pratinjau');
        $this->actingAs($admin)->get(route('admin.kuesioner-portal.export', $this->periode))
            ->assertOk()->assertDownload();

        $this->actingAs($admin)->post(route('admin.kuesioner-portal.eksklusi', $respons), ['dikecualikan' => 1, 'alasan_dikecualikan' => 'Bukan ASN'])
            ->assertRedirect();
        $this->assertTrue($respons->fresh()->dikecualikan);
    }

    public function test_setiap_tooltip_halaman_analisis_punya_penjelasan(): void
    {
        $view = file_get_contents(resource_path('views/admin/kuesioner-portal/analisis.blade.php'));
        preg_match_all("/_info', \['k' => '([A-Za-z_]+)'\]/", $view, $m);

        $kunci = array_merge(
            $m[1],
            ['masuk', 'menolak', 'belum_selesai', 'selesai', 'dianalisis', 'dianalisis_kano'],
            array_map(fn ($k) => "kuadran_$k", array_keys(Analisis::KUADRAN)),
            array_map(fn ($k) => "kano_$k", ['A', 'O', 'M', 'I', 'R', 'Q']),
        );

        $this->assertGreaterThan(20, count($m[1]));
        foreach ($kunci as $k) {
            $this->assertNotSame('', \App\Services\KuesionerPortal\Penjelasan::get($k), "Penjelasan '$k' belum ditulis");
        }
    }

    public function test_seeder_simulasi_memakai_akun_pemohon_dan_idempoten(): void
    {
        $pemohon = collect(range(1, 6))->map(fn () => $this->responden());
        $bukanPemohon = $this->responden(pernahMengajukan: false);

        $this->seed(\Database\Seeders\KuesionerPortalSimulasiSeeder::class);
        $this->seed(\Database\Seeders\KuesionerPortalSimulasiSeeder::class);

        $simulasi = KuesionerPortalPeriode::where('nama', \Database\Seeders\KuesionerPortalSimulasiSeeder::NAMA_PERIODE)->sole();
        $this->assertFalse($simulasi->is_active);
        $this->assertTrue($this->periode->fresh()->is_active, 'Periode sungguhan tidak boleh ikut ditutup');
        $this->assertEqualsCanonicalizing($pemohon->pluck('id')->all(), $simulasi->responses()->pluck('user_id')->all());
        $this->assertFalse($simulasi->responses()->where('user_id', $bukanPemohon->id)->exists());

        $selesai = $simulasi->responses()->selesai()->withCount('answers')->get();
        $this->assertNotEmpty($selesai);
        $this->assertTrue($selesai->every(fn ($r) => $r->answers_count === 24));
    }

    public function test_seeder_simulasi_menolak_berjalan_di_produksi(): void
    {
        $this->responden();
        $this->app['env'] = 'production';

        (new \Database\Seeders\KuesionerPortalSimulasiSeeder)->run();

        $this->assertFalse(KuesionerPortalPeriode::where('nama', \Database\Seeders\KuesionerPortalSimulasiSeeder::NAMA_PERIODE)->exists());
    }

    public function test_hanya_satu_periode_yang_dibuka(): void
    {
        $admin = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert(['role_id' => DB::table('roles')->where('name', 'Admin')->value('id'), 'user_id' => $admin->id]);
        $baru = KuesionerPortalPeriode::create(['nama' => 'Periode 2']);

        $this->actingAs($admin)->post(route('admin.kuesioner-portal.periode.buka', $baru))->assertRedirect();

        $this->assertFalse($this->periode->fresh()->is_active);
        $this->assertTrue($baru->fresh()->is_active);
        $this->assertSame($baru->id, KuesionerPortalPeriode::aktif()->id);
    }

    public function test_admin_bukan_responden(): void
    {
        $admin = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert(['role_id' => DB::table('roles')->where('name', 'Admin')->value('id'), 'user_id' => $admin->id]);
        DB::table('requests')->insert(['user_id' => $admin->id, 'service' => 'uji', 'created_at' => now(), 'updated_at' => now()]);

        $this->assertFalse(app(\App\Services\KuesionerPortal\Kelayakan::class)->memenuhi($admin));
    }

    // ------------------------------------------------------------------

    private function responden(bool $pernahMengajukan = true): User
    {
        $user = User::factory()->create(['is_verified' => true, 'unit_kerja_id' => $this->unitKerja->id]);
        DB::table('role_user')->insert(['role_id' => DB::table('roles')->where('name', 'User')->value('id'), 'user_id' => $user->id]);

        if ($pernahMengajukan) {
            DB::table('requests')->insert(['user_id' => $user->id, 'service' => 'uji', 'created_at' => now(), 'updated_at' => now()]);
        }

        return $user;
    }

    private function payload(bool $hubungiPetugas = true): array
    {
        $data = [
            'unit_kerja_id' => $this->unitKerja->id,
            'status_kepegawaian' => 'pns',
            'peran' => 'operator',
            'lama_penggunaan' => '1_2t',
            'frekuensi' => 'bulanan',
            'layanan_diajukan' => ['email', 'subdomain'],
            'pernah_hubungi_petugas' => $hubungiPetugas ? '1' : '0',
            'kelebihan' => 'Satu pintu',
            'kekurangan' => null,
            'saran' => 'Notifikasi WhatsApp',
        ];
        foreach (Instrumen::kodeAtribut() as $kode) {
            $data['kepentingan'][$kode] = 4;
            $data['kinerja'][$kode] = 4;
            $data['kano'][$kode] = ['f' => 1, 'd' => 5];
        }
        foreach (Instrumen::kodeKepuasan() as $kode) {
            $data['kepuasan'][$kode] = 5;
        }

        return $data;
    }

    /**
     * @param  callable(string): array{0:int,1:int,2:int,3:int}  $nilai  [kepentingan, kinerja, F, D]
     */
    private function responsSelesai(callable $nilai, int $kepuasan = 4, int $durasi = 900): KuesionerPortalResponse
    {
        $user = $this->responden();
        $respons = KuesionerPortalResponse::create([
            'periode_id' => $this->periode->id,
            'user_id' => $user->id,
            'kode_responden' => KuesionerPortalResponse::kodeBaru(),
            'status' => KuesionerPortalResponse::STATUS_SELESAI,
            'persetujuan_at' => now()->subSeconds($durasi),
            'submitted_at' => now(),
            'durasi_detik' => $durasi,
            'unit_kerja_id' => $this->unitKerja->id,
            'status_kepegawaian' => 'pns',
            'peran' => 'staf',
            'lama_penggunaan' => 'gt2t',
            'frekuensi' => 'mingguan',
            'layanan_diajukan' => ['email'],
            'pernah_hubungi_petugas' => true,
        ]);
        foreach (Instrumen::kodeAtribut() as $kode) {
            [$imp, $perf, $f, $d] = $nilai($kode);
            $respons->answers()->create(['kode' => $kode, 'kepentingan' => $imp, 'kinerja' => $perf, 'kano_fungsional' => $f, 'kano_disfungsional' => $d]);
        }
        foreach (Instrumen::kodeKepuasan() as $kode) {
            $respons->answers()->create(['kode' => $kode, 'kinerja' => $kepuasan]);
        }

        return $respons;
    }
}
