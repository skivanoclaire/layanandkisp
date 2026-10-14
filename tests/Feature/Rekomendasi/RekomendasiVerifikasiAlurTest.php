<?php

namespace Tests\Feature\Rekomendasi;

use App\Models\RekomendasiAplikasiForm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Alur tombol verifikasi di halaman detail usulan (admin).
 * Dulu "Proses Verifikasi" pada usulan yang verifikasinya masih "menunggu" berujung 405,
 * karena halaman verify mengalihkan (GET) ke route start yang hanya menerima POST.
 */
class RekomendasiVerifikasiAlurTest extends TestCase
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

        // Model otomatis membuat record verifikasi berstatus "menunggu".
        // fresh(): hook "created" sempat memuat relasi verifikasi saat masih kosong.
        $this->usulan = RekomendasiAplikasiForm::factory()->create(['status' => 'diajukan'])->fresh();
    }

    public function test_usulan_menunggu_menampilkan_tombol_mulai_verifikasi_lewat_post(): void
    {
        $this->assertSame('menunggu', $this->usulan->verifikasi->status);

        $this->actingAs($this->admin)
            ->get(route('admin.rekomendasi.verifikasi.show', $this->usulan->id))
            ->assertOk()
            ->assertSee('action="' . route('admin.rekomendasi.verifikasi.start', $this->usulan->id) . '"', false)
            ->assertDontSee('href="' . route('admin.rekomendasi.verifikasi.verify', $this->usulan->id) . '"', false);
    }

    public function test_halaman_verify_tidak_lagi_mengalihkan_ke_route_post(): void
    {
        $respons = $this->actingAs($this->admin)
            ->get(route('admin.rekomendasi.verifikasi.verify', $this->usulan->id));

        $respons->assertRedirect(route('admin.rekomendasi.verifikasi.show', $this->usulan->id));
        $this->followRedirects($respons)->assertOk();
    }

    public function test_mulai_verifikasi_membuka_halaman_checklist_dan_tombol_lanjut_tetap_ada(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.rekomendasi.verifikasi.start', $this->usulan->id))
            ->assertRedirect(route('admin.rekomendasi.verifikasi.verify', $this->usulan->id));

        $this->assertSame('sedang_diverifikasi', $this->usulan->fresh()->verifikasi->status);

        $this->get(route('admin.rekomendasi.verifikasi.verify', $this->usulan->id))
            ->assertOk()
            ->assertSee('Checklist Verifikasi');

        // Setelah dimulai status usulan menjadi "diproses"; tombol lanjut harus tetap muncul di detail.
        $this->get(route('admin.rekomendasi.verifikasi.show', $this->usulan->id))
            ->assertOk()
            ->assertSee('href="' . route('admin.rekomendasi.verifikasi.verify', $this->usulan->id) . '"', false);
    }

    public function test_usulan_ditolak_dapat_diverifikasi_ulang(): void
    {
        $this->actingAs($this->admin)->post(route('admin.rekomendasi.verifikasi.start', $this->usulan->id));
        $this->post(route('admin.rekomendasi.verifikasi.reject', $this->usulan->id), ['catatan_verifikasi' => 'Tidak sesuai'])
            ->assertSessionHas('success');
        $this->usulan->update(['rejected_by' => $this->admin->id, 'rejected_at' => now()]);

        $this->assertSame('ditolak', $this->usulan->fresh()->status);

        $this->get(route('admin.rekomendasi.verifikasi.show', $this->usulan->id))
            ->assertOk()
            ->assertSee('Verifikasi Ulang')
            ->assertSee('action="' . route('admin.rekomendasi.verifikasi.start', $this->usulan->id) . '"', false);

        $this->get(route('admin.rekomendasi.verifikasi.index', ['status' => 'ditolak']))
            ->assertOk()
            ->assertSee('Verifikasi Ulang');

        $this->post(route('admin.rekomendasi.verifikasi.start', $this->usulan->id))
            ->assertRedirect(route('admin.rekomendasi.verifikasi.verify', $this->usulan->id));

        $usulan = $this->usulan->fresh();
        $this->assertSame('diproses', $usulan->status);
        $this->assertSame('verifikasi', $usulan->fase_saat_ini);
        $this->assertNull($usulan->rejected_at);
        $this->assertSame('sedang_diverifikasi', $usulan->verifikasi->status);
        $this->assertSame('Tidak sesuai', $usulan->verifikasi->catatan_verifikasi);
        $this->assertNull($usulan->verifikasi->tanggal_verifikasi);

        $this->get(route('admin.rekomendasi.verifikasi.verify', $usulan->id))
            ->assertOk()
            ->assertSee('Checklist Verifikasi');
    }

    public function test_usulan_disetujui_tidak_dapat_diverifikasi_ulang(): void
    {
        $this->usulan->update(['status' => 'disetujui']);

        $this->actingAs($this->admin)
            ->post(route('admin.rekomendasi.verifikasi.start', $this->usulan->id))
            ->assertSessionHas('error');

        $this->assertSame('disetujui', $this->usulan->fresh()->status);
    }
}
