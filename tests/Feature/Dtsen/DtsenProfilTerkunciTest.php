<?php

namespace Tests\Feature\Dtsen;

use App\Models\DtsenAccountRequest;
use App\Models\DtsenDataRequest;
use App\Models\DtsenVariable;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Identitas pemohon pada form DTSEN mengikuti profil pengguna.
 *
 * Kolom yang profilnya sudah terisi dikunci di tampilan sekaligus ditimpa di sisi
 * server, sehingga pemohon tidak perlu mengetik ulang dan nilai tersimpan tidak
 * bisa menyimpang dari profil. Kolom yang profilnya masih kosong tetap terbuka.
 */
class DtsenProfilTerkunciTest extends TestCase
{
    use RefreshDatabase;

    private User $pemohon;
    private UnitKerja $unitKerja;
    private UnitKerja $unitKerjaLain;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->unitKerja = UnitKerja::factory()->create(['is_active' => true, 'tipe' => UnitKerja::TIPE_INDUK]);
        $this->unitKerjaLain = UnitKerja::factory()->create(['is_active' => true, 'tipe' => UnitKerja::TIPE_INDUK]);

        $this->pemohon = User::factory()->create([
            'is_verified' => true,
            'name' => 'Budi Santoso',
            'email' => 'budi@kaltaraprov.go.id',
            'nip' => '199001012015011001',
            'phone' => '08123456789',
            'unit_kerja_id' => $this->unitKerja->id,
        ]);

        $roleId = DB::table('roles')->insertGetId([
            'name' => 'Pemohon DTSEN',
            'display_name' => 'Pemohon DTSEN',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('role_user')->insert(['role_id' => $roleId, 'user_id' => $this->pemohon->id]);
        DB::table('permission_role')->insert([
            'role_id' => $roleId,
            'permission_id' => DB::table('permissions')->where('name', 'Akses DTSEN')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ------------------------------------------------- Form akun (Tahap 1)

    public function test_form_akun_mengunci_kolom_yang_sudah_ada_di_profil(): void
    {
        $this->actingAs($this->pemohon)
            ->get(route('user.dtsen.akun.create'))
            ->assertOk()
            // Perangkat daerah tidak lagi berupa dropdown bebas.
            ->assertSee('Terisi dari profil Anda')
            ->assertSee('name="unit_kerja_id" value="' . $this->unitKerja->id . '"', false)
            ->assertDontSee('-- Pilih Perangkat Daerah --')
            ->assertSee('Budi Santoso');
    }

    public function test_akun_menolak_unit_kerja_yang_berbeda_dari_profil(): void
    {
        // Browser dipaksa mengirim perangkat daerah lain; profil tetap menang.
        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.akun.store'), $this->payloadAkun([
                'unit_kerja_id' => $this->unitKerjaLain->id,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(
            $this->unitKerja->id,
            DtsenAccountRequest::firstOrFail()->unit_kerja_id,
            'Perangkat daerah harus mengikuti profil, bukan kiriman browser.'
        );
    }

    public function test_personel_pertama_selalu_mengikuti_profil_pengaju(): void
    {
        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.akun.store'), $this->payloadAkun([
                'members' => [
                    // Identitas pengaju dipalsukan dari browser.
                    ['nama' => 'Orang Lain', 'nip' => '000', 'email' => 'palsu@example.com', 'no_hp' => '000'],
                    ['nama' => 'Rekan Satu Tim', 'email' => 'rekan@kaltaraprov.go.id'],
                ],
            ]))
            ->assertSessionHasNoErrors();

        $members = DtsenAccountRequest::firstOrFail()->members()->orderBy('id')->get();

        $this->assertCount(2, $members);
        $this->assertSame('Budi Santoso', $members[0]->nama);
        $this->assertSame('199001012015011001', $members[0]->nip);
        $this->assertSame('budi@kaltaraprov.go.id', $members[0]->email);
        $this->assertSame('08123456789', $members[0]->no_hp);
        $this->assertSame($this->unitKerja->nama, $members[0]->unit_kerja);

        // Personel tambahan tetap bebas diisi.
        $this->assertSame('Rekan Satu Tim', $members[1]->nama);
    }

    public function test_narahubung_mengikuti_profil_bila_ditandai_sama(): void
    {
        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.akun.store'), $this->payloadAkun([
                'narahubung_sama_profil' => 1,
                'narahubung_nama' => 'Diketik Manual',
                'narahubung_kontak' => '00000',
                'narahubung_email' => 'manual@example.com',
            ]))
            ->assertSessionHasNoErrors();

        $akun = DtsenAccountRequest::firstOrFail();
        $this->assertSame('Budi Santoso', $akun->narahubung_nama);
        $this->assertSame('08123456789', $akun->narahubung_kontak);
        $this->assertSame('budi@kaltaraprov.go.id', $akun->narahubung_email);
    }

    public function test_narahubung_dapat_diisi_orang_lain_bila_tidak_ditandai_sama(): void
    {
        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.akun.store'), $this->payloadAkun([
                'narahubung_nama' => 'Siti Aminah',
                'narahubung_kontak' => '08987654321',
                'narahubung_email' => 'siti@kaltaraprov.go.id',
            ]))
            ->assertSessionHasNoErrors();

        $akun = DtsenAccountRequest::firstOrFail();
        $this->assertSame('Siti Aminah', $akun->narahubung_nama);
        $this->assertSame('siti@kaltaraprov.go.id', $akun->narahubung_email);
    }

    public function test_perangkat_daerah_tetap_dapat_dipilih_bila_profil_kosong(): void
    {
        $this->pemohon->forceFill(['unit_kerja_id' => null])->save();

        $this->actingAs($this->pemohon)
            ->get(route('user.dtsen.akun.create'))
            ->assertOk()
            ->assertSee('-- Pilih Perangkat Daerah --')
            ->assertSee('belum diatur di profil Anda', false);

        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.akun.store'), $this->payloadAkun([
                'unit_kerja_id' => $this->unitKerjaLain->id,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame($this->unitKerjaLain->id, DtsenAccountRequest::firstOrFail()->unit_kerja_id);
    }

    // -------------------------------------------- Form permohonan (Tahap 2)

    public function test_form_permohonan_mengunci_identitas_pemohon_dari_profil(): void
    {
        $this->akunAktif();

        $this->actingAs($this->pemohon)
            ->get(route('user.dtsen.permohonan.create'))
            ->assertOk()
            ->assertSee('Diambil dari profil Anda')
            ->assertSee('value="Budi Santoso"', false)
            ->assertSee('value="199001012015011001"', false)
            ->assertSee('value="08123456789"', false);
    }

    public function test_identitas_pemohon_ditimpa_nilai_profil_saat_disimpan(): void
    {
        $this->akunAktif();

        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.permohonan.store'), $this->payloadPermohonan([
                'pemohon_nama' => 'Nama Palsu',
                'pemohon_nip' => '000000',
                'pemohon_telepon' => '000000',
            ]))
            ->assertSessionHasNoErrors();

        $permohonan = DtsenDataRequest::firstOrFail();
        $this->assertSame('Budi Santoso', $permohonan->pemohon_nama);
        $this->assertSame('199001012015011001', $permohonan->pemohon_nip);
        $this->assertSame('08123456789', $permohonan->pemohon_telepon);
    }

    public function test_jabatan_terisi_otomatis_bila_sudah_ada_di_profil(): void
    {
        Jabatan::create([
            'user_id' => $this->pemohon->id,
            'nama_jabatan' => 'Kepala Bidang Statistik',
            'unit_kerja_id' => $this->unitKerja->id,
        ]);
        $this->pemohon->refresh();
        $this->akunAktif();

        $this->actingAs($this->pemohon)
            ->get(route('user.dtsen.permohonan.create'))
            ->assertOk()
            ->assertSee('value="Kepala Bidang Statistik"', false);

        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.permohonan.store'), $this->payloadPermohonan([
                'pemohon_jabatan' => 'Jabatan Palsu',
            ]))->assertSessionHasNoErrors();

        $this->assertSame('Kepala Bidang Statistik', DtsenDataRequest::firstOrFail()->pemohon_jabatan);
    }

    public function test_jabatan_tetap_dapat_diketik_bila_belum_ada_di_profil(): void
    {
        $this->akunAktif();

        // Pengguna tanpa data jabatan diberi tahu agar melengkapi profil.
        $this->actingAs($this->pemohon)
            ->get(route('user.dtsen.permohonan.create'))
            ->assertOk()
            ->assertSee('Belum ada di profil Anda', false);

        $this->actingAs($this->pemohon)
            ->post(route('user.dtsen.permohonan.store'), $this->payloadPermohonan([
                'pemohon_jabatan' => 'Analis Kebijakan',
            ]))->assertSessionHasNoErrors();

        $this->assertSame('Analis Kebijakan', DtsenDataRequest::firstOrFail()->pemohon_jabatan);
    }

    // ------------------------------------------------------------ Helper

    private function akunAktif(): DtsenAccountRequest
    {
        return DtsenAccountRequest::create([
            'user_id' => $this->pemohon->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'nomor_surat' => '005/DTSEN/2026',
            'sifat_surat' => 'biasa',
            'tanggal_surat' => now()->toDateString(),
            'narahubung_nama' => 'Budi Santoso',
            'narahubung_kontak' => '08123456789',
            'narahubung_email' => 'budi@kaltaraprov.go.id',
            'status' => DtsenAccountRequest::STATUS_DISETUJUI,
            'submitted_at' => now(),
            'verified_at' => now(),
            'is_active' => true,
            'activated_at' => now(),
            'consent_true' => true,
        ]);
    }

    private function payloadAkun(array $timpa = []): array
    {
        return array_replace([
            'unit_kerja_id' => $this->unitKerja->id,
            'nomor_surat' => '005/DTSEN/2026',
            'sifat_surat' => 'biasa',
            'tanggal_surat' => now()->toDateString(),
            'surat' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
            'narahubung_nama' => 'Narahubung Teknis',
            'narahubung_kontak' => '08111111111',
            'narahubung_email' => 'narahubung@kaltaraprov.go.id',
            'members' => [
                ['nama' => 'Budi Santoso', 'email' => 'budi@kaltaraprov.go.id'],
            ],
            'consent_true' => 1,
            'action' => 'submit',
        ], $timpa);
    }

    private function payloadPermohonan(array $timpa = []): array
    {
        $variabel = DtsenVariable::where('level_minimal', 2)->firstOrFail();

        return array_replace([
            'pemohon_nama' => 'Budi Santoso',
            'pemohon_nip' => '199001012015011001',
            'pemohon_jabatan' => '',
            'pemohon_telepon' => '08123456789',
            'sifat_surat' => 'biasa',
            'nama_program' => 'Bantuan Sosial Daerah',
            'jenis_permintaan' => 'bnba',
            'tujuan_penggunaan' => 'Penetapan sasaran penerima bantuan.',
            'surat_permohonan' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
            'variabel' => [$variabel->id],
            'kegunaan' => [$variabel->id => 'Rekap agregat wilayah.'],
            'consent_true' => 1,
            'action' => 'submit',
        ], $timpa);
    }
}
