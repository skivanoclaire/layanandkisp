<?php

namespace Tests\Feature\Rekomendasi;

use App\Models\RekomendasiAplikasiForm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Petunjuk Pengisian dan unduhan format dokumen (.docx) tiap tahap tampil
 * di halaman fase pengembangan pemohon maupun admin.
 */
class FasePengembanganPetunjukTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private RekomendasiAplikasiForm $usulan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert([
            'role_id' => Role::where('name', 'Admin')->value('id'),
            'user_id' => $this->admin->id,
        ]);

        $this->usulan = RekomendasiAplikasiForm::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'disetujui',
            'fase_saat_ini' => 'pengembangan',
        ]);
    }

    public function test_setiap_tahap_punya_berkas_format_docx(): void
    {
        $tahap = config('fase_pengembangan.tahap');

        $this->assertSame(
            ['rancang_bangun', 'implementasi', 'uji_kelaikan', 'pemeliharaan', 'evaluasi'],
            array_keys($tahap)
        );

        foreach ($tahap as $fase => $data) {
            $this->assertNotEmpty($data['petunjuk'], "Petunjuk $fase kosong");
            $this->assertFileExists(
                public_path(config('fase_pengembangan.folder_format') . '/' . $data['format']),
                "Berkas format $fase tidak ada"
            );
        }
    }

    public function test_halaman_admin_menampilkan_petunjuk_dan_unduhan_format(): void
    {
        $this->assertHalamanMemuatPetunjuk(
            $this->actingAs($this->admin)->get(route('admin.fase-pengembangan.show', $this->usulan->id))
        );
    }

    public function test_halaman_pemohon_tetap_menampilkan_petunjuk_dan_unduhan_format(): void
    {
        $this->assertHalamanMemuatPetunjuk(
            $this->actingAs($this->admin)->get(route('fase-pengembangan.show', $this->usulan->id))
        );
    }

    private function assertHalamanMemuatPetunjuk($respons): void
    {
        $respons->assertOk()->assertSee('Petunjuk Pengisian');

        foreach (config('fase_pengembangan.tahap') as $data) {
            $respons->assertSee(strtok($data['petunjuk'], "\n"));
            $respons->assertSee(
                asset(config('fase_pengembangan.folder_format') . '/' . $data['format']),
                false
            );
        }
    }
}
