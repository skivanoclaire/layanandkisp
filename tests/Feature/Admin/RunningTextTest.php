<?php

namespace Tests\Feature\Admin;

use App\Models\RunningText;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RunningTextTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'        => 'admin',
            'is_verified' => true,
        ]);

        // Permission admin.running-text sudah diberikan ke role Admin oleh
        // 2026_08_31_000002_create_running_text_permission.
        $roleId = DB::table('roles')->where('name', 'Admin')->value('id');
        DB::table('role_user')->insertOrIgnore([
            'role_id' => $roleId,
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_admin_bisa_menambah_running_text(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.running-text.store'), [
            'isi'       => 'Layanan email dijeda Senin pukul 08.00 WITA.',
            'tautan'    => 'https://kaltaraprov.go.id/pengumuman',
            'urutan'    => 3,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.running-text.index'));
        $this->assertDatabaseHas('running_texts', [
            'isi'        => 'Layanan email dijeda Senin pukul 08.00 WITA.',
            'tautan'     => 'https://kaltaraprov.go.id/pengumuman',
            'urutan'     => 3,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_tag_html_dibuang_saat_disimpan(): void
    {
        $this->actingAs($this->admin)->post(route('admin.running-text.store'), [
            'isi'       => '<script>alert(1)</script><b>Pengumuman</b> penting',
            'is_active' => '1',
        ]);

        $tersimpan = RunningText::latest('id')->first();

        $this->assertNotNull($tersimpan);
        $this->assertStringNotContainsString('<', $tersimpan->isi);
        $this->assertStringContainsString('Pengumuman penting', $tersimpan->isi);
    }

    public function test_tautan_dengan_skema_berbahaya_ditolak(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.running-text.create'))
            ->post(route('admin.running-text.store'), [
                'isi'       => 'Uji tautan berbahaya',
                'tautan'    => 'javascript:alert(1)',
                'is_active' => '1',
            ]);

        $response->assertSessionHasErrors('tautan');
        $this->assertDatabaseCount('running_texts', 0);
    }

    public function test_running_text_aktif_tampil_di_navbar_dan_ter_escape(): void
    {
        RunningText::create([
            'isi'       => 'Pemeliharaan "server" <malam ini>',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.running-text.index'));

        $response->assertOk();
        $response->assertSee('Informasi berjalan', false);
        // Tanda kurung siku sudah dibuang saat sanitasi, sisanya tetap di-escape Blade.
        $response->assertSee('Pemeliharaan &quot;server&quot;', false);
        $response->assertDontSee('<malam ini>', false);
    }

    public function test_running_text_di_luar_jadwal_tidak_tampil(): void
    {
        RunningText::create([
            'isi'        => 'Pengumuman kedaluwarsa',
            'is_active'  => true,
            'selesai_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.running-text.index'));

        // Tetap terlihat di tabel admin, tapi tidak ikut tayang di bar navbar.
        $this->assertTrue(RunningText::untukNavbar()->isEmpty());
        $response->assertDontSee('Informasi berjalan', false);
    }

    public function test_non_admin_tidak_bisa_mengakses(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_verified' => true]);

        $this->actingAs($user)->get(route('admin.running-text.index'))->assertForbidden();
    }

    public function test_toggle_mengubah_status_aktif(): void
    {
        $rt = RunningText::create(['isi' => 'Teks uji toggle', 'is_active' => true]);

        $this->actingAs($this->admin)
            ->patch(route('admin.running-text.toggle', $rt))
            ->assertRedirect();

        $this->assertFalse($rt->fresh()->is_active);
    }
}
