<?php

namespace Tests\Feature\Dtsen;

use App\Models\DtsenDataRequest;
use App\Models\DtsenRelease;
use App\Models\DtsenVariable;
use App\Models\DtsenWilayah;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Master data DTSEN: katalog variabel, rilis, dan wilayah berjenjang.
 *
 * Fokus pada pengaman yang menjaga keutuhan jejak permohonan lama —
 * data acuan yang sudah pernah dipakai tidak boleh hilang begitu saja.
 */
class DtsenMasterDataTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_verified' => true]);

        $roleId = DB::table('roles')->insertGetId([
            'name' => 'Pengelola Master DTSEN',
            'display_name' => 'Pengelola Master DTSEN',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('role_user')->insert(['role_id' => $roleId, 'user_id' => $this->admin->id]);

        $permissions = DB::table('permissions')
            ->whereIn('name', ['admin.dtsen.variables', 'admin.dtsen.releases', 'admin.dtsen.wilayah'])
            ->pluck('id');

        foreach ($permissions as $permissionId) {
            DB::table('permission_role')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function test_variabel_yang_pernah_dimohonkan_dinonaktifkan_bukan_dihapus(): void
    {
        $variabel = DtsenVariable::firstOrFail();
        $permohonan = $this->permohonanContoh();
        $permohonan->requestVariables()->create([
            'dtsen_variable_id' => $variabel->id,
            'kegunaan' => 'Dasar penetapan sasaran.',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.dtsen.variables.destroy', $variabel->id))
            ->assertRedirect();

        // Jejak permohonan lama harus tetap utuh.
        $this->assertDatabaseHas('dtsen_variables', ['id' => $variabel->id, 'is_active' => false]);
        $this->assertDatabaseHas('dtsen_request_variables', [
            'dtsen_data_request_id' => $permohonan->id,
            'dtsen_variable_id' => $variabel->id,
        ]);
    }

    public function test_variabel_yang_belum_pernah_dipakai_dapat_dihapus(): void
    {
        $variabel = DtsenVariable::create([
            'kode' => 'DTSEN-999',
            'nama' => 'Variabel Uji Coba',
            'level_minimal' => 2,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.dtsen.variables.destroy', $variabel->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('dtsen_variables', ['id' => $variabel->id]);
    }

    public function test_variabel_dapat_dinonaktifkan_dan_hilang_dari_katalog_pemohon(): void
    {
        $variabel = DtsenVariable::firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.dtsen.variables.toggle', $variabel->id))
            ->assertRedirect();

        $this->assertFalse($variabel->refresh()->is_active);
        $this->assertFalse(
            DtsenVariable::active()->where('id', $variabel->id)->exists(),
            'Variabel nonaktif tidak boleh muncul pada katalog pemilihan.'
        );
    }

    public function test_level_permohonan_mengikuti_variabel_dengan_level_tertinggi(): void
    {
        $level2 = DtsenVariable::where('level_minimal', 2)->firstOrFail();
        $level3 = DtsenVariable::where('level_minimal', 3)->firstOrFail();
        $level4 = DtsenVariable::where('level_minimal', 4)->firstOrFail();

        $this->assertSame(2, DtsenDataRequest::highestLevelFor([$level2->id]));
        $this->assertSame(3, DtsenDataRequest::highestLevelFor([$level2->id, $level3->id]));
        $this->assertSame(4, DtsenDataRequest::highestLevelFor([$level2->id, $level4->id]));
        // Tanpa variabel, sistem memakai level terendah yang dilayani alur permohonan.
        $this->assertSame(2, DtsenDataRequest::highestLevelFor([]));
    }

    public function test_rilis_yang_masih_memuat_variabel_tidak_dapat_dihapus(): void
    {
        $rilis = DtsenRelease::firstOrFail();

        $this->actingAs($this->admin)
            ->delete(route('admin.dtsen.releases.destroy', $rilis->id))
            ->assertSessionHasErrors('rilis');

        $this->assertDatabaseHas('dtsen_releases', ['id' => $rilis->id]);
    }

    public function test_pemberitahuan_rilis_baru_tercatat_pada_permohonan_pemegang_data(): void
    {
        $permohonan = $this->permohonanContoh();
        $permohonan->update([
            'status' => DtsenDataRequest::STATUS_DATA_TERSEDIA,
            'akses_at' => now(),
            'dtsen_release_id' => DtsenRelease::firstOrFail()->id,
        ]);

        $rilisBaru = DtsenRelease::create([
            'nomor_rilis' => 'DTSEN-2026-01',
            'tanggal_rilis' => now()->toDateString(),
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.dtsen.releases.notify', $rilisBaru->id))
            ->assertRedirect();

        $this->assertNotNull($rilisBaru->refresh()->notified_at);
        $this->assertDatabaseHas('dtsen_request_logs', [
            'request_type' => 'permohonan',
            'request_id' => $permohonan->id,
            'action' => 'rilis_baru',
        ]);
    }

    public function test_wilayah_yang_masih_punya_turunan_tidak_dapat_dihapus(): void
    {
        $kabupaten = DtsenWilayah::where('tingkat', 'kabupaten_kota')->firstOrFail();
        $this->assertTrue($kabupaten->children()->exists(), 'Prasyarat: kabupaten punya kecamatan.');

        $this->actingAs($this->admin)
            ->delete(route('admin.dtsen.wilayah.destroy', $kabupaten->id))
            ->assertSessionHasErrors('wilayah');

        $this->assertDatabaseHas('dtsen_wilayahs', ['id' => $kabupaten->id]);
    }

    public function test_wilayah_menampilkan_jalur_berjenjang(): void
    {
        $kecamatan = DtsenWilayah::where('tingkat', 'kecamatan')->with('parent.parent')->firstOrFail();

        $this->assertStringContainsString('Kalimantan Utara', $kecamatan->jalur);
        $this->assertStringContainsString($kecamatan->nama, $kecamatan->jalur);
        $this->assertSame(3, substr_count($kecamatan->jalur, '/') + 1);
    }

    public function test_wilayah_tidak_boleh_menjadi_induk_bagi_dirinya_sendiri(): void
    {
        $wilayah = DtsenWilayah::where('tingkat', 'kabupaten_kota')->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.dtsen.wilayah.update', $wilayah->id), [
                'parent_id' => $wilayah->id,
                'tingkat' => $wilayah->tingkat,
                'nama' => $wilayah->nama,
                'is_active' => 1,
            ])->assertRedirect();

        $this->assertNull($wilayah->refresh()->parent_id);
    }

    public function test_kode_variabel_harus_unik(): void
    {
        $existing = DtsenVariable::firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.dtsen.variables.store'), [
                'kode' => $existing->kode,
                'nama' => 'Duplikat',
                'level_minimal' => 2,
            ])->assertSessionHasErrors('kode');
    }

    // ------------------------------------------------------------ Helper

    private function permohonanContoh(): DtsenDataRequest
    {
        $unitKerja = UnitKerja::factory()->create(['is_active' => true, 'tipe' => UnitKerja::TIPE_INDUK]);
        $pemohon = User::factory()->create(['is_verified' => true, 'unit_kerja_id' => $unitKerja->id]);

        return DtsenDataRequest::create([
            'user_id' => $pemohon->id,
            'unit_kerja_id' => $unitKerja->id,
            'pemohon_nama' => $pemohon->name,
            'pemohon_telepon' => '08123456789',
            'nama_program' => 'Program Uji',
            'jenis_permintaan' => 'bnba',
            'tujuan_penggunaan' => 'Pengujian master data.',
            'level_akses' => 4,
            'status' => DtsenDataRequest::STATUS_DIAJUKAN,
            'submitted_at' => now(),
            'consent_true' => true,
        ]);
    }
}
