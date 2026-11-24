<?php

namespace Tests\Feature\Rekomendasi;

use App\Models\RekomendasiAplikasiForm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Admin mengubah status usulan dari halaman verifikasi, mis. pemohon tidak melakukan revisi.
 */
class KoreksiStatusDariVerifikasiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert([
            'role_id' => Role::where('name', 'Admin')->value('id'),
            'user_id' => $this->admin->id,
        ]);
    }

    private function usulanPerluRevisi(): RekomendasiAplikasiForm
    {
        $usulan = RekomendasiAplikasiForm::factory()->create(['status' => 'diajukan'])->fresh();
        $usulan->update([
            'status' => 'perlu_revisi',
            'revision_notes' => 'Lengkapi data',
            'revision_requested_by' => $this->admin->id,
            'revision_requested_at' => now()->subDays(20),
        ]);

        return $usulan->fresh();
    }

    public function test_halaman_verifikasi_menampilkan_tombol_ubah_status_dan_lama_revisi(): void
    {
        $usulan = $this->usulanPerluRevisi();

        $this->actingAs($this->admin)->get(route('admin.rekomendasi.verifikasi.show', $usulan->id))
            ->assertOk()
            ->assertSee('Ubah Status Usulan')
            ->assertSee('Revisi diminta sejak')
            ->assertSee(route('admin.rekomendasi.verifikasi.koreksi-status', $usulan->id), false);
    }

    public function test_perlu_revisi_dapat_dikembalikan_ke_antrean_dari_verifikasi(): void
    {
        $usulan = $this->usulanPerluRevisi();

        $this->actingAs($this->admin)
            ->post(route('admin.rekomendasi.verifikasi.koreksi-status', $usulan->id), [
                'new_status' => 'diajukan',
                'catatan' => 'Pemohon tidak merevisi, dikembalikan ke antrean',
            ])
            ->assertRedirect(route('admin.rekomendasi.verifikasi.show', $usulan->id))
            ->assertSessionHas('success');

        $this->assertSame('diajukan', $usulan->refresh()->status);
    }

    public function test_perlu_revisi_dapat_ditolak_dari_verifikasi(): void
    {
        $usulan = $this->usulanPerluRevisi();

        $this->actingAs($this->admin)
            ->post(route('admin.rekomendasi.verifikasi.koreksi-status', $usulan->id), [
                'new_status' => 'ditolak',
                'catatan' => 'Tidak ada tindak lanjut revisi',
            ])
            ->assertRedirect(route('admin.rekomendasi.verifikasi.show', $usulan->id));

        $this->assertSame('ditolak', $usulan->refresh()->status);
    }

    public function test_tujuan_di_luar_aturan_ditolak(): void
    {
        $usulan = $this->usulanPerluRevisi();

        $this->actingAs($this->admin)
            ->post(route('admin.rekomendasi.verifikasi.koreksi-status', $usulan->id), [
                'new_status' => 'disetujui',
                'catatan' => 'Mencoba lompat status',
            ])
            ->assertSessionHasErrors('new_status');

        $this->assertSame('perlu_revisi', $usulan->refresh()->status);
    }
}
