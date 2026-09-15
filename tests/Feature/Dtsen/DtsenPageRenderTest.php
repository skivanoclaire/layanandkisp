<?php

namespace Tests\Feature\Dtsen;

use App\Models\DtsenAccountRequest;
use App\Models\DtsenDataRequest;
use App\Models\DtsenVariable;
use App\Models\DtsenWilayah;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Smoke test render seluruh halaman DTSEN.
 *
 * Menangkap kesalahan runtime pada blade (variabel tak terdefinisi, relasi belum
 * di-eager-load, nama route salah) yang tidak terlihat dari uji alur berbasis POST.
 */
class DtsenPageRenderTest extends TestCase
{
    use RefreshDatabase;

    private User $pemohon;
    private User $admin;
    private DtsenDataRequest $permohonan;
    private DtsenAccountRequest $akun;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $unitKerja = UnitKerja::factory()->create(['is_active' => true, 'tipe' => UnitKerja::TIPE_INDUK]);

        $this->pemohon = User::factory()->create([
            'is_verified' => true,
            'unit_kerja_id' => $unitKerja->id,
            'phone' => '08123456789',
        ]);

        // Satu akun admin memegang seluruh permission DTSEN, termasuk verifikasi
        // substansi, supaya semua halaman internal dapat diuji dalam satu sesi.
        $this->admin = User::factory()->create(['is_verified' => true]);

        $this->grant($this->pemohon, 'Pemohon', ['Akses DTSEN']);
        $this->grant($this->admin, 'Pengelola DTSEN', [
            'Kelola Akun DTSEN', 'Kelola Permohonan DTSEN', 'Verifikasi Substansi DTSEN',
            'Kelola Laporan DTSEN', 'admin.dtsen.dashboard', 'admin.dtsen.variables',
            'admin.dtsen.releases', 'admin.dtsen.wilayah',
        ]);

        $this->akun = DtsenAccountRequest::create([
            'user_id' => $this->pemohon->id,
            'unit_kerja_id' => $unitKerja->id,
            'nomor_surat' => '005/DTSEN/2026',
            'sifat_surat' => 'biasa',
            'tanggal_surat' => now()->toDateString(),
            'narahubung_nama' => 'Narahubung',
            'narahubung_kontak' => '08123456789',
            'narahubung_email' => 'narahubung@kaltaraprov.go.id',
            'status' => DtsenAccountRequest::STATUS_DISETUJUI,
            'submitted_at' => now(),
            'verified_at' => now(),
            'is_active' => true,
            'activated_at' => now(),
            'last_used_at' => now(),
            'consent_true' => true,
        ]);

        $this->akun->members()->create([
            'nama' => $this->pemohon->name,
            'email' => $this->pemohon->email,
            'jabatan' => 'Pranata Komputer',
        ]);

        $variabel = DtsenVariable::where('level_minimal', 4)->firstOrFail();
        $wilayah = DtsenWilayah::where('tingkat', 'kabupaten_kota')->firstOrFail();

        $this->permohonan = DtsenDataRequest::create([
            'user_id' => $this->pemohon->id,
            'unit_kerja_id' => $unitKerja->id,
            'dtsen_account_request_id' => $this->akun->id,
            'pemohon_nama' => $this->pemohon->name,
            'pemohon_telepon' => '08123456789',
            'nama_program' => 'Bantuan Sosial Daerah',
            'jenis_permintaan' => 'bnba',
            'cakupan_wilayah_ids' => [$wilayah->id],
            'tujuan_penggunaan' => 'Penetapan sasaran penerima bantuan.',
            'level_akses' => 4,
            'status' => DtsenDataRequest::STATUS_VERIF_SUBSTANSI,
            'submitted_at' => now(),
            'verif_admin_at' => now(),
            'kak_latar_belakang' => 'Latar belakang.',
            'kak_maksud_tujuan' => 'Maksud dan tujuan.',
            'kak_dasar_hukum' => ['Perda No. 1 Tahun 2020'],
            'kak_personel_akses' => [['nama' => 'Analis', 'nip' => '', 'jabatan' => 'Analis']],
            'kak_teknik_pelindungan' => ['masking'],
            'consent_true' => true,
        ]);

        $this->permohonan->requestVariables()->create([
            'dtsen_variable_id' => $variabel->id,
            'kegunaan' => 'Dasar penetapan sasaran.',
        ]);

        $this->permohonan->documents()->create([
            'jenis' => 'pendukung',
            'nama_dokumen' => 'proposal.pdf',
            'file_path' => 'dtsen-docs/pendukung/proposal.pdf',
            'uploaded_by' => $this->pemohon->id,
        ]);

        $this->permohonan->clarifications()->create([
            'tanggal' => now()->toDateString(),
            'tempat_media' => 'Ruang Rapat Bapperida',
            'peserta' => 'Tim Pelaksana & OPD',
            'pokok_klarifikasi' => 'Kesesuaian variabel dengan tusi.',
            'created_by' => $this->admin->id,
        ]);
    }

    /** @dataProvider halamanPemohon */
    public function test_halaman_pemohon_dapat_dirender(string $routeName, bool $butuhId = false): void
    {
        $url = $butuhId ? route($routeName, $this->idUntuk($routeName)) : route($routeName);

        $this->actingAs($this->pemohon)->get($url)->assertOk();
    }

    public static function halamanPemohon(): array
    {
        return [
            'daftar akun' => ['user.dtsen.akun.index'],
            'form akun' => ['user.dtsen.akun.create'],
            'detail akun' => ['user.dtsen.akun.show', true],
            'daftar permohonan' => ['user.dtsen.permohonan.index'],
            'form permohonan' => ['user.dtsen.permohonan.create'],
            'detail permohonan' => ['user.dtsen.permohonan.show', true],
            'daftar perpanjangan' => ['user.dtsen.perpanjangan.index'],
            'daftar pemanfaatan' => ['user.dtsen.pemanfaatan.index'],
            'daftar pemusnahan' => ['user.dtsen.pemusnahan.index'],
            'daftar insiden' => ['user.dtsen.insiden.index'],
            'form insiden' => ['user.dtsen.insiden.create'],
            'daftar pengaduan' => ['user.dtsen.pengaduan.index'],
            'form pengaduan' => ['user.dtsen.pengaduan.create'],
        ];
    }

    /** @dataProvider halamanAdmin */
    public function test_halaman_internal_dapat_dirender(string $routeName, bool $butuhId = false): void
    {
        $url = $butuhId ? route($routeName, $this->idUntuk($routeName)) : route($routeName);

        $this->actingAs($this->admin)->get($url)->assertOk();
    }

    public static function halamanAdmin(): array
    {
        return [
            'daftar akun' => ['admin.dtsen.akun.index'],
            'detail akun' => ['admin.dtsen.akun.show', true],
            'daftar permohonan' => ['admin.dtsen.permohonan.index'],
            'detail permohonan' => ['admin.dtsen.permohonan.show', true],
            'daftar substansi' => ['admin.dtsen.substansi.index'],
            'detail substansi' => ['admin.dtsen.substansi.show', true],
            'daftar perpanjangan' => ['admin.dtsen.perpanjangan.index'],
            'rekap pemanfaatan' => ['admin.dtsen.laporan.pemanfaatan'],
            'rekap pemusnahan' => ['admin.dtsen.laporan.pemusnahan'],
            'rekap insiden' => ['admin.dtsen.laporan.insiden'],
            'daftar pengaduan' => ['admin.dtsen.pengaduan.index'],
            'dashboard' => ['admin.dtsen.dashboard'],
            'master variabel' => ['admin.dtsen.variables.index'],
            'tambah variabel' => ['admin.dtsen.variables.create'],
            'master rilis' => ['admin.dtsen.releases.index'],
            'tambah rilis' => ['admin.dtsen.releases.create'],
            'master wilayah' => ['admin.dtsen.wilayah.index'],
        ];
    }

    public function test_form_permohonan_ulang_menyalin_permohonan_sebelumnya(): void
    {
        $this->permohonan->update([
            'status' => DtsenDataRequest::STATUS_DATA_TERSEDIA,
            'akses_at' => now(),
        ]);

        $this->actingAs($this->pemohon)
            ->get(route('user.dtsen.permohonan.create', ['ulang_dari' => $this->permohonan->id]))
            ->assertOk()
            ->assertSee($this->permohonan->ticket_no)
            ->assertSee('Permintaan ulang / pembaruan data');
    }

    public function test_form_pemanfaatan_dan_pemusnahan_terbuka_setelah_data_tersedia(): void
    {
        $this->permohonan->update([
            'status' => DtsenDataRequest::STATUS_DATA_TERSEDIA,
            'akses_at' => now(),
        ]);

        $this->actingAs($this->pemohon)->get(route('user.dtsen.pemanfaatan.create'))->assertOk();
        $this->actingAs($this->pemohon)->get(route('user.dtsen.pemusnahan.create'))->assertOk();
    }

    public function test_halaman_akses_token_menampilkan_hitung_mundur(): void
    {
        $token = $this->permohonan->tokens()->create([
            'token' => \App\Models\DtsenAccessToken::generateToken(),
            'metode' => 'excel_terenkripsi',
            'file_path' => 'dtsen-data/data.xlsx',
            'nama_berkas' => 'data.xlsx',
            'issued_by' => $this->admin->id,
            'issued_at' => now(),
            'expires_at' => now()->addDays(DtsenDataRequest::TOKEN_ACTIVE_DAYS),
        ]);

        $this->actingAs($this->pemohon)
            ->get(route('user.dtsen.akses.show', $token->token))
            ->assertOk()
            ->assertSee('hari tersisa');
    }

    public function test_form_ubah_akun_terbuka_saat_dikembalikan(): void
    {
        $this->akun->update([
            'status' => DtsenAccountRequest::STATUS_DIKEMBALIKAN,
            'catatan_perbaikan' => 'Surat belum ditandatangani Kepala Perangkat Daerah.',
        ]);

        $this->actingAs($this->pemohon)
            ->get(route('user.dtsen.akun.edit', $this->akun->id))
            ->assertOk()
            ->assertSee('Catatan perbaikan dari verifikator')
            ->assertSee($this->pemohon->name);
    }

    public function test_halaman_edit_hanya_terbuka_saat_draft_atau_perlu_perbaikan(): void
    {
        // Sedang diverifikasi substansi — pemohon tidak boleh mengubah dokumen.
        $this->actingAs($this->pemohon)
            ->get(route('user.dtsen.permohonan.edit', $this->permohonan->id))
            ->assertForbidden();

        $this->permohonan->update(['status' => DtsenDataRequest::STATUS_PERLU_PERBAIKAN]);

        $this->actingAs($this->pemohon)
            ->get(route('user.dtsen.permohonan.edit', $this->permohonan->id))
            ->assertOk();
    }

    public function test_halaman_substansi_menunjukkan_tahap_lanjutan_setelah_diterima(): void
    {
        $this->permohonan->update([
            'status' => DtsenDataRequest::STATUS_DITERIMA,
            'sub_hasil' => 'diterima',
            'verif_substansi_at' => now(),
            'diterima_at' => now(),
        ]);

        // Halaman substansi tidak boleh jadi jalan buntu: harus mengarahkan ke
        // pemrosesan & QA di sisi DKISP.
        $this->actingAs($this->admin)
            ->get(route('admin.dtsen.substansi.show', $this->permohonan->id))
            ->assertOk()
            ->assertSee('Tahap Berikutnya')
            ->assertSee('Lanjut ke Pemrosesan')
            ->assertSee(route('admin.dtsen.permohonan.show', $this->permohonan->id), false);
    }

    public function test_halaman_permohonan_menjelaskan_saat_menunggu_verifikasi_substansi(): void
    {
        // Status awal berkas contoh memang sedang menunggu verifikasi substansi.
        $this->actingAs($this->admin)
            ->get(route('admin.dtsen.permohonan.show', $this->permohonan->id))
            ->assertOk()
            ->assertSee('Menunggu Verifikasi Substansi')
            ->assertSee('Bapperida');
    }

    public function test_pemohon_tidak_dapat_membuka_halaman_internal(): void
    {
        $this->actingAs($this->pemohon)
            ->get(route('admin.dtsen.permohonan.index'))
            ->assertForbidden();

        $this->actingAs($this->pemohon)
            ->get(route('admin.dtsen.substansi.index'))
            ->assertForbidden();
    }

    // ------------------------------------------------------------ Helper

    private function idUntuk(string $routeName): int
    {
        return str_contains($routeName, '.akun.') ? $this->akun->id : $this->permohonan->id;
    }

    private function grant(User $user, string $roleName, array $permissions): void
    {
        $roleId = DB::table('roles')->insertGetId([
            'name' => $roleName,
            'display_name' => $roleName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('role_user')->insert(['role_id' => $roleId, 'user_id' => $user->id]);

        foreach (DB::table('permissions')->whereIn('name', $permissions)->pluck('id') as $permissionId) {
            DB::table('permission_role')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
