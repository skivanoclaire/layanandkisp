<?php

namespace Tests\Feature\Rekomendasi;

use App\Models\RekomendasiAplikasiForm;
use App\Models\User;
use App\Models\UnitKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekomendasiUsulanStoreTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected UnitKerja $unitKerja;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and unit kerja
        $this->user = User::factory()->create([
            'role' => 'User',
            'name' => 'Test User',
            'email' => 'testuser@example.com',
        ]);

        $this->unitKerja = UnitKerja::factory()->create([
            'is_active' => true,
            'tipe' => UnitKerja::TIPE_INDUK,
        ]);
    }

    /**
     * Helper method to get valid form data
     */
    protected function getValidFormData(array $overrides = []): array
    {
        return array_merge([
            'nama_aplikasi' => 'Test Application',
            'deskripsi' => '<p>Test description</p>',
            'tujuan' => '<p>Test purpose</p>',
            'manfaat' => '<p>Test benefits</p>',
            'pemilik_proses_bisnis_id' => $this->unitKerja->id,
            'jenis_layanan' => 'publik',
            'target_pengguna' => 'All users',
            'estimasi_pengguna' => 1000,
            'lingkup_aplikasi' => 'regional',
            'platform' => ['web', 'mobile'],
            'teknologi_diusulkan' => 'Laravel',
            'estimasi_waktu_pengembangan' => 6,
            'estimasi_biaya' => 50000000,
            'sumber_pendanaan' => 'apbd',
            'prioritas' => 'tinggi',
        ], $overrides);
    }

    /** @test */
    public function user_can_create_draft_with_pelaksana_pembangunan_menteri()
    {
        $data = $this->getValidFormData([
            'pelaksana_pembangunan' => 'menteri',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->post(route('user.rekomendasi.usulan.store'), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('rekomendasi_aplikasi_forms', [
            'nama_aplikasi' => 'Test Application',
            'user_id' => $this->user->id,
            'status' => 'draft',
            'pelaksana_pembangunan' => 'menteri',
        ]);
    }

    /** @test */
    public function user_can_create_draft_with_pelaksana_pembangunan_swakelola()
    {
        $data = $this->getValidFormData([
            'pelaksana_pembangunan' => 'swakelola',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->post(route('user.rekomendasi.usulan.store'), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('rekomendasi_aplikasi_forms', [
            'nama_aplikasi' => 'Test Application',
            'pelaksana_pembangunan' => 'swakelola',
        ]);
    }

    /** @test */
    public function user_can_create_draft_with_pelaksana_pembangunan_pihak_ketiga()
    {
        $data = $this->getValidFormData([
            'pelaksana_pembangunan' => 'pihak_ketiga',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->post(route('user.rekomendasi.usulan.store'), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('rekomendasi_aplikasi_forms', [
            'nama_aplikasi' => 'Test Application',
            'pelaksana_pembangunan' => 'pihak_ketiga',
        ]);
    }

    /** @test */
    public function user_can_create_draft_without_pelaksana_pembangunan_nullable_field()
    {
        $data = $this->getValidFormData();
        // Don't include pelaksana_pembangunan field

        $response = $this
            ->actingAs($this->user)
            ->post(route('user.rekomendasi.usulan.store'), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('rekomendasi_aplikasi_forms', [
            'nama_aplikasi' => 'Test Application',
            'user_id' => $this->user->id,
        ]);

        // Verify pelaksana_pembangunan is null
        $proposal = RekomendasiAplikasiForm::where('nama_aplikasi', 'Test Application')->first();
        $this->assertNull($proposal->pelaksana_pembangunan);
    }

    /** @test */
    public function validation_rejects_invalid_pelaksana_pembangunan_value()
    {
        $data = $this->getValidFormData([
            'pelaksana_pembangunan' => 'invalid_value',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->post(route('user.rekomendasi.usulan.store'), $data);

        $response->assertSessionHasErrors('pelaksana_pembangunan');
    }

    /** @test */
    public function proposal_is_created_with_draft_status()
    {
        $data = $this->getValidFormData();

        $response = $this
            ->actingAs($this->user)
            ->post(route('user.rekomendasi.usulan.store'), $data);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('rekomendasi_aplikasi_forms', [
            'nama_aplikasi' => 'Test Application',
            'status' => 'draft',
            'fase_saat_ini' => 'usulan',
        ]);
    }

    /** @test */
    public function proposal_is_created_with_auto_generated_ticket_number()
    {
        $data = $this->getValidFormData();

        $response = $this
            ->actingAs($this->user)
            ->post(route('user.rekomendasi.usulan.store'), $data);

        $response->assertSessionHasNoErrors();

        $proposal = RekomendasiAplikasiForm::where('nama_aplikasi', 'Test Application')->first();
        $this->assertNotNull($proposal->ticket_number);
        $this->assertStringStartsWith('TKT-REK-APL-', $proposal->ticket_number);
    }

    /** @test */
    public function required_fields_validation_works()
    {
        $response = $this
            ->actingAs($this->user)
            ->post(route('user.rekomendasi.usulan.store'), [
                'nama_aplikasi' => '', // Missing required field
            ]);

        $response->assertSessionHasErrors([
            'nama_aplikasi',
            'deskripsi',
            'tujuan',
            'manfaat',
            'pemilik_proses_bisnis_id',
            'jenis_layanan',
            'target_pengguna',
            'estimasi_pengguna',
            'lingkup_aplikasi',
            'platform',
            'estimasi_waktu_pengembangan',
            'estimasi_biaya',
            'sumber_pendanaan',
            'prioritas',
        ]);
    }

    /** @test */
    public function database_stores_correct_pelaksana_pembangunan_value()
    {
        $testValues = ['menteri', 'swakelola', 'pihak_ketiga'];

        foreach ($testValues as $value) {
            $data = $this->getValidFormData([
                'nama_aplikasi' => "Test Application - {$value}",
                'pelaksana_pembangunan' => $value,
            ]);

            $response = $this
                ->actingAs($this->user)
                ->post(route('user.rekomendasi.usulan.store'), $data);

            $response->assertSessionHasNoErrors();

            $proposal = RekomendasiAplikasiForm::where('nama_aplikasi', "Test Application - {$value}")->first();
            $this->assertEquals($value, $proposal->pelaksana_pembangunan,
                "Failed asserting pelaksana_pembangunan stored correctly for value: {$value}");
        }
    }

    /** @test */
    public function user_can_update_pelaksana_pembangunan_on_existing_draft()
    {
        // Create draft with 'swakelola'
        $proposal = RekomendasiAplikasiForm::factory()
            ->withPelaksanaSwakelola()
            ->create([
                'user_id' => $this->user->id,
                'status' => 'draft',
            ]);

        // Update to 'menteri'
        $updateData = $this->getValidFormData([
            'nama_aplikasi' => $proposal->nama_aplikasi,
            'pelaksana_pembangunan' => 'menteri',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->put(route('user.rekomendasi.usulan.update', $proposal->id), $updateData);

        $response->assertSessionHasNoErrors();

        $proposal->refresh();
        $this->assertEquals('menteri', $proposal->pelaksana_pembangunan);
    }

    /** @test */
    public function complete_form_submission_with_all_permenkomdigi_fields()
    {
        $data = $this->getValidFormData([
            'pelaksana_pembangunan' => 'menteri',
            'dasar_hukum' => '<p>Test legal basis</p>',
            'uraian_permasalahan' => '<p>Test problem description</p>',
            'pihak_terkait' => '<p>Test stakeholders</p>',
            'ruang_lingkup' => '<p>Test scope</p>',
            'analisis_biaya_manfaat' => '<p>Test cost-benefit analysis</p>',
            'lokasi_implementasi' => 'Kalimantan Utara',
            'uraian_ruang_lingkup' => '<p>Test scope description</p>',
            'proses_bisnis' => '<p>Test business process</p>',
            'kerangka_kerja' => '<p>Test framework</p>',
            'peran_tanggung_jawab' => '<p>Test roles and responsibilities</p>',
            'jadwal_pelaksanaan' => '<p>Test schedule</p>',
            'rencana_aksi' => '<p>Test action plan</p>',
            'keamanan_informasi' => '<p>Test information security</p>',
            'sumber_daya_manusia' => '<p>Test human resources</p>',
            'sumber_daya_anggaran' => '<p>Test budget resources</p>',
            'sumber_daya_sarana' => '<p>Test infrastructure resources</p>',
            'indikator_keberhasilan' => '<p>Test success indicators</p>',
            'alih_pengetahuan' => '<p>Test knowledge transfer</p>',
            'pemantauan_pelaporan' => '<p>Test monitoring and reporting</p>',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->post(route('user.rekomendasi.usulan.store'), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('rekomendasi_aplikasi_forms', [
            'nama_aplikasi' => 'Test Application',
            'pelaksana_pembangunan' => 'menteri',
            'lokasi_implementasi' => 'Kalimantan Utara',
        ]);
    }
}
