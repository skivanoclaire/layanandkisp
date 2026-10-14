<?php

namespace Tests\Feature\Rekomendasi;

use App\Models\RekomendasiAplikasiForm;
use App\Models\RekomendasiHistoriAktivitas;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Koreksi status usulan oleh admin di /admin/rekomendasi/{id},
 * mis. usulan yang ditolak dikembalikan ke "perlu revisi".
 */
class RekomendasiKoreksiStatusTest extends TestCase
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

    private function usulanDitolak(): RekomendasiAplikasiForm
    {
        $usulan = RekomendasiAplikasiForm::factory()->create(['status' => 'diajukan'])->fresh();

        $usulan->verifikasi->update(['status' => 'ditolak', 'catatan_verifikasi' => 'Tidak sesuai', 'tanggal_verifikasi' => now()]);
        $usulan->update([
            'status' => 'ditolak',
            'fase_saat_ini' => 'usulan',
            'admin_feedback' => 'Tidak sesuai',
            'rejected_by' => $this->admin->id,
            'rejected_at' => now(),
        ]);

        return $usulan->fresh();
    }

    public function test_usulan_ditolak_dapat_dikembalikan_ke_perlu_revisi(): void
    {
        $usulan = $this->usulanDitolak();

        $this->actingAs($this->admin)->get(route('admin.rekomendasi.show', $usulan->id))
            ->assertOk()
            ->assertSee('Koreksi Status')
            ->assertSee('action="' . route('admin.rekomendasi.koreksi-status', $usulan->id) . '"', false);

        $this->actingAs($this->admin)
            ->post(route('admin.rekomendasi.koreksi-status', $usulan->id), [
                'new_status' => 'perlu_revisi',
                'catatan' => 'Lengkapi analisis kebutuhan',
            ])
            ->assertRedirect(route('admin.rekomendasi.show', $usulan->id))
            ->assertSessionHas('success');

        $usulan->refresh();
        $this->assertSame('perlu_revisi', $usulan->status);
        $this->assertSame('usulan', $usulan->fase_saat_ini);
        $this->assertSame('Lengkapi analisis kebutuhan', $usulan->revision_notes);
        $this->assertSame($this->admin->id, $usulan->revision_requested_by);
        $this->assertNull($usulan->rejected_at);
        $this->assertSame('perlu_revisi', $usulan->verifikasi->status);
        $this->assertSame('Lengkapi analisis kebutuhan', $usulan->verifikasi->catatan_verifikasi);
        $this->assertTrue(
            RekomendasiHistoriAktivitas::where('rekomendasi_aplikasi_form_id', $usulan->id)->where('aktivitas', 'Koreksi Status')->exists()
        );
    }

    public function test_usulan_ditolak_dapat_dikembalikan_ke_antrean_verifikasi(): void
    {
        $usulan = $this->usulanDitolak();

        $this->actingAs($this->admin)
            ->post(route('admin.rekomendasi.koreksi-status', $usulan->id), ['new_status' => 'diajukan', 'catatan' => 'Salah tolak'])
            ->assertSessionHas('success');

        $usulan->refresh();
        $this->assertSame('diajukan', $usulan->status);
        $this->assertSame('verifikasi', $usulan->fase_saat_ini);
        $this->assertNull($usulan->rejected_by);
        $this->assertSame('menunggu', $usulan->verifikasi->status);
        $this->assertNull($usulan->verifikasi->catatan_verifikasi);
    }

    public function test_catatan_wajib_dan_status_tujuan_dibatasi(): void
    {
        $usulan = $this->usulanDitolak();

        $this->actingAs($this->admin)
            ->post(route('admin.rekomendasi.koreksi-status', $usulan->id), ['new_status' => 'perlu_revisi'])
            ->assertSessionHasErrors('catatan');

        $this->actingAs($this->admin)
            ->post(route('admin.rekomendasi.koreksi-status', $usulan->id), ['new_status' => 'disetujui', 'catatan' => 'x'])
            ->assertSessionHasErrors('new_status');

        $this->assertSame('ditolak', $usulan->fresh()->status);
    }

    public function test_usulan_disetujui_tidak_dapat_dikoreksi(): void
    {
        $usulan = RekomendasiAplikasiForm::factory()->create(['status' => 'diajukan'])->fresh();
        $usulan->update(['status' => 'disetujui', 'approved_by' => $this->admin->id, 'approved_at' => now()]);

        $this->actingAs($this->admin)->get(route('admin.rekomendasi.show', $usulan->id))
            ->assertOk()
            ->assertDontSee('Koreksi Status');

        $this->actingAs($this->admin)
            ->post(route('admin.rekomendasi.koreksi-status', $usulan->id), ['new_status' => 'perlu_revisi', 'catatan' => 'x'])
            ->assertSessionHas('error');

        $this->assertSame('disetujui', $usulan->fresh()->status);
    }

    public function test_pengguna_tanpa_izin_tidak_dapat_mengoreksi(): void
    {
        $usulan = $this->usulanDitolak();
        $user = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert(['role_id' => Role::where('name', 'User')->value('id'), 'user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('admin.rekomendasi.koreksi-status', $usulan->id), ['new_status' => 'perlu_revisi', 'catatan' => 'x'])
            ->assertForbidden();

        $this->assertSame('ditolak', $usulan->fresh()->status);
    }
}
