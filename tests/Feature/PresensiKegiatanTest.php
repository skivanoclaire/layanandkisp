<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\PresensiKegiatan;
use App\Models\PresensiKegiatanPeserta;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Konfirmasi kehadiran & presensi kegiatan (publik) serta pengelolaannya oleh Admin.
 */
class PresensiKegiatanTest extends TestCase
{
    use RefreshDatabase;

    private const TTD = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private UnitKerja $dkisp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dkisp = UnitKerja::factory()->create([
            'nama' => 'DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN',
            'tipe' => UnitKerja::TIPE_INDUK,
        ]);
    }

    private function kegiatan(array $ubah = []): PresensiKegiatan
    {
        return PresensiKegiatan::create($ubah + [
            'slug' => 'rapat-uji',
            'judul' => 'Rapat Uji',
            'tanggal_mulai' => '2026-11-04',
            'tempat' => 'Aula',
            'konfirmasi_aktif' => true,
            'presensi_aktif' => true,
            'wajib_tanda_tangan' => true,
        ]);
    }

    private function pengguna(string $role, array $ubah = []): User
    {
        $user = User::factory()->create($ubah + ['is_verified' => true]);
        DB::table('role_user')->insert(['role_id' => Role::where('name', $role)->value('id'), 'user_id' => $user->id]);

        return $user;
    }

    private function isian(array $ubah = []): array
    {
        return $ubah + [
            'nama' => 'Budi Santoso, S.Kom',
            'nip' => '19900101 201501 1 001',
            'jabatan' => 'Pranata Komputer',
            'pilihan_instansi' => (string) $this->dkisp->id,
            'no_hp' => '+62 812-3456-7890',
        ];
    }

    public function test_workshop_dari_surat_undangan_tersedia_dan_nonaktif(): void
    {
        $workshop = PresensiKegiatan::where('slug', 'workshop-tik-2026')->first();

        $this->assertNotNull($workshop);
        $this->assertFalse($workshop->konfirmasi_aktif);
        $this->assertFalse($workshop->presensi_aktif);
        $this->assertSame(1, $workshop->kuota_per_instansi);

        $this->get('/kegiatan/workshop-tik-2026')->assertOk()->assertSee('Workshop Aplikasi Portal Layanan TIK');
        $this->get('/kegiatan/workshop-tik-2026/konfirmasi')->assertOk()->assertSee('Konfirmasi kehadiran sedang ditutup');
    }

    public function test_konfirmasi_manual_tersimpan_dengan_data_ternormalisasi(): void
    {
        $kegiatan = $this->kegiatan();

        $this->post(route('kegiatan.konfirmasi.simpan', $kegiatan), $this->isian(['konfirmasi' => 'hadir']))
            ->assertRedirect(route('kegiatan.selesai', $kegiatan));

        $p = PresensiKegiatanPeserta::sole();
        $this->assertSame('199001012015011001', $p->nip);
        $this->assertSame('081234567890', $p->no_hp);
        $this->assertSame($this->dkisp->id, $p->unit_kerja_id);
        $this->assertSame('hadir', $p->konfirmasi);
        $this->assertSame('manual', $p->sumber);
        $this->assertNull($p->hadir_at);
    }

    public function test_instansi_dipilih_dari_unit_kerja_pemprov_atau_lainnya(): void
    {
        $kegiatan = $this->kegiatan();
        $pusat = UnitKerja::factory()->create(['nama' => 'OTORITAS JASA KEUANGAN', 'tipe' => UnitKerja::TIPE_PUSAT]);

        $this->get(route('kegiatan.konfirmasi', $kegiatan))->assertOk()
            ->assertSee('<option value="' . $this->dkisp->id . '"', false)
            ->assertDontSee('<option value="' . $pusat->id . '"', false)
            ->assertSee('Lainnya (tulis manual)');

        // Unit kerja di luar Pemprov tidak bisa dipilih lewat id.
        $this->post(route('kegiatan.konfirmasi.simpan', $kegiatan), $this->isian([
            'pilihan_instansi' => (string) $pusat->id, 'konfirmasi' => 'hadir',
        ]))->assertSessionHasErrors('instansi');

        // "Lainnya" tanpa nama instansi ditolak.
        $this->post(route('kegiatan.konfirmasi.simpan', $kegiatan), $this->isian([
            'pilihan_instansi' => 'lainnya', 'instansi_lainnya' => '  ', 'konfirmasi' => 'hadir',
        ]))->assertSessionHasErrors('instansi');

        $this->post(route('kegiatan.konfirmasi.simpan', $kegiatan), $this->isian([
            'pilihan_instansi' => 'lainnya', 'instansi_lainnya' => 'Diskominfo Kabupaten Bulungan', 'konfirmasi' => 'online',
        ]))->assertSessionHasNoErrors();

        $p = PresensiKegiatanPeserta::sole();
        $this->assertSame('Diskominfo Kabupaten Bulungan', $p->instansi);
        $this->assertSame('online', $p->konfirmasi);
        $this->assertNull($p->unit_kerja_id);
    }

    public function test_hadir_online_langsung_dikirimi_tautan_via_wa_aptika(): void
    {
        config(['services.fonnte.tokens.aptika' => 'aptika-token', 'services.fonnte.url' => 'https://fonnte.test/send']);
        Http::fake(['fonnte.test/*' => Http::response(['status' => true], 200)]);
        $kegiatan = $this->kegiatan(['tautan_online' => 'https://zoom.us/j/123456', 'keterangan_online' => 'Passcode: kaltara']);

        $this->post(route('kegiatan.konfirmasi.simpan', $kegiatan), $this->isian(['konfirmasi' => 'online']))
            ->assertRedirect(route('kegiatan.selesai', $kegiatan));
        $this->get(route('kegiatan.selesai', $kegiatan))->assertSee('telah dikirim ke WhatsApp');

        Http::assertSentCount(1);
        Http::assertSent(fn ($req) => $req->hasHeader('Authorization', 'aptika-token')
            && $req['target'] === '081234567890'
            && str_contains($req['message'], 'https://zoom.us/j/123456')
            && str_contains($req['message'], 'Passcode: kaltara')
            && str_contains($req['message'], route('kegiatan.presensi', $kegiatan)));
        $this->assertNotNull(PresensiKegiatanPeserta::sole()->wa_online_terkirim_at);

        // Konfirmasi ulang tidak mengirim WA dua kali.
        $this->post(route('kegiatan.konfirmasi.simpan', $kegiatan), $this->isian(['konfirmasi' => 'online']));
        Http::assertSentCount(1);
    }

    public function test_admin_mengirim_tautan_ke_peserta_online_yang_belum_menerima(): void
    {
        config(['services.fonnte.tokens.aptika' => 'aptika-token', 'services.fonnte.url' => 'https://fonnte.test/send']);
        Http::fake(['fonnte.test/*' => Http::response(['status' => true], 200)]);
        $kegiatan = $this->kegiatan();

        // Tautan belum diisi: konfirmasi online tersimpan tanpa WA.
        $this->post(route('kegiatan.konfirmasi.simpan', $kegiatan), $this->isian(['konfirmasi' => 'online']));
        $this->post(route('kegiatan.konfirmasi.simpan', $kegiatan), $this->isian([
            'nama' => 'Peserta Lokasi', 'nip' => '', 'no_hp' => '082222222222', 'konfirmasi' => 'hadir',
            'pilihan_instansi' => 'lainnya', 'instansi_lainnya' => 'Instansi Lain',
        ]));
        $this->get(route('kegiatan.selesai', $kegiatan));
        Http::assertNothingSent();

        $admin = $this->pengguna('Admin');
        $this->actingAs($admin)->post(route('admin.presensi-kegiatan.kirim-tautan-online', $kegiatan))->assertSessionHas('error');

        $kegiatan->update(['tautan_online' => 'https://meet.google.com/abc-defg-hij']);
        $this->actingAs($admin)->post(route('admin.presensi-kegiatan.kirim-tautan-online', $kegiatan))->assertSessionHas('success');
        Http::assertSentCount(1); // hanya peserta online

        // Tidak ada lagi yang belum menerima; "kirim ulang" mengirim lagi ke semua peserta online.
        $this->actingAs($admin)->post(route('admin.presensi-kegiatan.kirim-tautan-online', $kegiatan));
        Http::assertSentCount(1);
        $this->actingAs($admin)->post(route('admin.presensi-kegiatan.kirim-tautan-online', $kegiatan), ['ulang' => 1]);
        Http::assertSentCount(2);

        $online = PresensiKegiatanPeserta::where('konfirmasi', 'online')->sole();
        $this->actingAs($admin)->post(route('admin.presensi-kegiatan.peserta.kirim-tautan', [$kegiatan, $online]))->assertSessionHas('success');
        Http::assertSentCount(3);
    }

    public function test_presensi_memperbarui_baris_konfirmasi_yang_sama(): void
    {
        $kegiatan = $this->kegiatan();
        $this->post(route('kegiatan.konfirmasi.simpan', $kegiatan), $this->isian(['konfirmasi' => 'hadir']));

        $this->post(route('kegiatan.presensi.simpan', $kegiatan), $this->isian(['tanda_tangan' => self::TTD]))
            ->assertRedirect(route('kegiatan.selesai', $kegiatan));

        $p = PresensiKegiatanPeserta::sole();
        $this->assertNotNull($p->hadir_at);
        $this->assertSame(self::TTD, $p->tanda_tangan);
    }

    public function test_presensi_menolak_tanpa_tanda_tangan_dan_saat_ditutup(): void
    {
        $kegiatan = $this->kegiatan();

        $this->post(route('kegiatan.presensi.simpan', $kegiatan), $this->isian())
            ->assertSessionHasErrors('tanda_tangan');

        $kegiatan->update(['presensi_aktif' => false]);
        $this->post(route('kegiatan.presensi.simpan', $kegiatan), $this->isian(['tanda_tangan' => self::TTD]))
            ->assertSessionHas('error');

        $this->assertSame(0, PresensiKegiatanPeserta::count());
    }

    public function test_kuota_per_instansi_dibatasi_saat_konfirmasi(): void
    {
        $kegiatan = $this->kegiatan(['kuota_per_instansi' => 1]);
        $this->post(route('kegiatan.konfirmasi.simpan', $kegiatan), $this->isian(['konfirmasi' => 'hadir']));

        $this->post(route('kegiatan.konfirmasi.simpan', $kegiatan), $this->isian([
            'nama' => 'Orang Kedua', 'nip' => '', 'no_hp' => '081111111111', 'konfirmasi' => 'online',
        ]))->assertSessionHasErrors('instansi');

        // Peserta pertama tetap boleh mengubah konfirmasinya sendiri.
        $this->post(route('kegiatan.konfirmasi.simpan', $kegiatan), $this->isian(['konfirmasi' => 'hadir']))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, PresensiKegiatanPeserta::count());
    }

    public function test_pengguna_login_datanya_diambil_dari_akun(): void
    {
        $user = $this->pengguna('User', [
            'name' => 'Siti Aminah', 'nip' => '198501012010012002', 'phone' => '081299998888', 'unit_kerja_id' => $this->dkisp->id,
        ]);
        Jabatan::create(['user_id' => $user->id, 'nama_jabatan' => 'Analis Sistem']);

        $kegiatan = $this->kegiatan();
        $this->actingAs($user)->get(route('kegiatan.presensi', $kegiatan))
            ->assertOk()->assertSee('Siti Aminah')->assertSee('Analis Sistem');

        // Nama/NIP/instansi palsu dari form diabaikan.
        $this->actingAs($user)->post(route('kegiatan.presensi.simpan', $kegiatan), [
            'nama' => 'Orang Lain', 'nip' => '111111111111111111', 'instansi' => 'Instansi Palsu',
            'jabatan' => 'Analis Sistem', 'no_hp' => '081299998888', 'tanda_tangan' => self::TTD,
        ])->assertRedirect(route('kegiatan.selesai', $kegiatan));

        $p = PresensiKegiatanPeserta::sole();
        $this->assertSame('Siti Aminah', $p->nama);
        $this->assertSame('198501012010012002', $p->nip);
        $this->assertSame($this->dkisp->nama, $p->instansi);
        $this->assertSame($user->id, $p->user_id);
        $this->assertSame('akun', $p->sumber);
    }

    public function test_masuk_dari_halaman_presensi_kembali_ke_form_setelah_login(): void
    {
        $kegiatan = $this->kegiatan();
        $user = $this->pengguna('User', ['password' => bcrypt('rahasia123')]);

        $this->get(route('kegiatan.masuk', [$kegiatan, 'presensi']))->assertRedirect(route('login'));

        $this->post('/login', ['email' => $user->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('kegiatan.presensi', $kegiatan));
    }

    public function test_hanya_admin_yang_bisa_mengelola(): void
    {
        $kegiatan = $this->kegiatan();

        $this->actingAs($this->pengguna('User'))
            ->get(route('admin.presensi-kegiatan.index'))
            ->assertForbidden();

        $admin = $this->pengguna('Admin');
        $this->actingAs($admin)->get(route('admin.presensi-kegiatan.index'))->assertOk()->assertSee('Rapat Uji');
        $this->get(route('kegiatan.konfirmasi', $kegiatan))->assertSee('Hadir online')->assertDontSee('Berhalangan');
        $this->actingAs($admin)->get(route('admin.presensi-kegiatan.show', $kegiatan))->assertOk();
        $this->actingAs($admin)->get(route('admin.presensi-kegiatan.qr', $kegiatan))->assertOk()->assertSee('<svg', false);

        $this->actingAs($admin)->post(route('admin.presensi-kegiatan.toggle', $kegiatan), ['kolom' => 'presensi_aktif']);
        $this->assertFalse($kegiatan->fresh()->presensi_aktif);
    }

    public function test_admin_membuat_kegiatan_baru_dengan_slug_otomatis(): void
    {
        $this->actingAs($this->pengguna('Admin'))->post(route('admin.presensi-kegiatan.store'), [
            'judul' => 'Sosialisasi Keamanan Informasi',
            'tanggal_mulai' => '2026-12-01',
            'tempat' => 'Ruang Rapat DKISP',
            'konfirmasi_aktif' => '1',
            'presensi_aktif' => '0',
            'wajib_tanda_tangan' => '1',
        ])->assertRedirect();

        $k = PresensiKegiatan::where('judul', 'Sosialisasi Keamanan Informasi')->sole();
        $this->assertSame('sosialisasi-keamanan-informasi', $k->slug);
        $this->assertTrue($k->konfirmasi_aktif);
        $this->assertFalse($k->presensi_aktif);
    }

    public function test_rekap_pdf_dan_excel_bisa_diunduh(): void
    {
        $kegiatan = $this->kegiatan(['pj_nama' => 'Pejabat Uji', 'pj_jabatan' => 'Kepala Bidang']);
        $this->post(route('kegiatan.presensi.simpan', $kegiatan), $this->isian(['tanda_tangan' => self::TTD]));
        $admin = $this->pengguna('Admin');

        foreach (['hadir', 'konfirmasi'] as $jenis) {
            $res = $this->actingAs($admin)->get(route('admin.presensi-kegiatan.rekap', [$kegiatan, 'jenis' => $jenis]));
            $res->assertOk();
            $this->assertStringStartsWith('%PDF', $res->getContent());
        }

        $this->actingAs($admin)->get(route('admin.presensi-kegiatan.ekspor', $kegiatan))->assertOk()->assertDownload('Peserta-rapat-uji.xlsx');
    }
}
