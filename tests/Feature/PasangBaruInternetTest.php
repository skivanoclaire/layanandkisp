<?php

namespace Tests\Feature;

use App\Models\InternetInstallationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PasangBaruInternetTest extends TestCase
{
    use RefreshDatabase;

    private User $pemohon;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pemohon = User::factory()->create(['is_verified' => true, 'nip' => '199001012020011001']);
        $this->admin = User::factory()->create(['is_verified' => true, 'nip' => '199001012020011001']);
        $this->beriPermission($this->pemohon, 'Akses Pasang Baru Internet', 'User');
        $this->beriPermission($this->admin, 'Kelola Pasang Baru Internet', 'Admin');
    }

    private function beriPermission(User $user, string $permission, string $role): void
    {
        $roleId = DB::table('roles')->where('name', $role)->value('id')
            ?? DB::table('roles')->insertGetId(['name' => $role, 'display_name' => $role, 'created_at' => now(), 'updated_at' => now()]);

        $permId = DB::table('permissions')->where('name', $permission)->value('id')
            ?? DB::table('permissions')->insertGetId([
                'name' => $permission, 'display_name' => $permission, 'group' => 'Test', 'order' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);

        DB::table('permission_role')->insertOrIgnore([
            'role_id' => $roleId, 'permission_id' => $permId, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('role_user')->insertOrIgnore(['role_id' => $roleId, 'user_id' => $user->id]);
    }

    private function dataPermohonan(array $override = []): array
    {
        return array_merge([
            'no_hp' => '081234567890',
            'lokasi_pemasangan' => 'Gedung Kantor Dinas, Lantai 2',
            'ruangan' => 'Ruang Bidang TIK',
            'pic_lokasi' => 'Budi',
            'jumlah_titik' => 3,
            'uraian_kebutuhan' => 'Untuk tiga perangkat kerja di ruang bidang',
        ], $override);
    }

    public function test_pemohon_bisa_mengajukan_pasang_baru_berstatus_menunggu(): void
    {
        $this->actingAs($this->pemohon)
            ->post(route('user.internet.pasang-baru.store'), $this->dataPermohonan())
            ->assertRedirect(route('user.internet.pasang-baru.index'))
            ->assertSessionHasNoErrors();

        $permohonan = InternetInstallationRequest::first();
        $this->assertNotNull($permohonan);
        $this->assertSame('menunggu', $permohonan->status);
        $this->assertStringStartsWith('PB-', $permohonan->ticket_no);
        $this->assertSame(3, $permohonan->jumlah_titik);
    }

    public function test_jumlah_titik_di_atas_batas_ditolak(): void
    {
        $this->actingAs($this->pemohon)
            ->post(route('user.internet.pasang-baru.store'), $this->dataPermohonan(['jumlah_titik' => 50]))
            ->assertSessionHasErrors('jumlah_titik');

        $this->assertSame(0, InternetInstallationRequest::count());
    }

    public function test_alur_status_menunggu_ke_proses_ke_selesai(): void
    {
        $permohonan = $this->buatPermohonan('menunggu');

        $this->actingAs($this->admin)
            ->post(route('admin.internet.pasang-baru.process', $permohonan->id))
            ->assertSessionHas('success');
        $this->assertSame('proses', $permohonan->fresh()->status);

        $this->actingAs($this->admin)
            ->post(route('admin.internet.pasang-baru.complete', $permohonan->id))
            ->assertSessionHas('success');

        $fresh = $permohonan->fresh();
        $this->assertSame('selesai', $fresh->status);
        $this->assertNotNull($fresh->completed_at);
        $this->assertSame($this->admin->id, $fresh->processed_by);
    }

    public function test_selesai_tidak_bisa_langsung_dari_menunggu(): void
    {
        $permohonan = $this->buatPermohonan('menunggu');

        $this->actingAs($this->admin)
            ->post(route('admin.internet.pasang-baru.complete', $permohonan->id))
            ->assertSessionHas('error');

        $this->assertSame('menunggu', $permohonan->fresh()->status);
    }

    public function test_admin_wajib_mengisi_alasan_penolakan(): void
    {
        $permohonan = $this->buatPermohonan('menunggu');

        $this->actingAs($this->admin)
            ->post(route('admin.internet.pasang-baru.reject', $permohonan->id), ['admin_notes' => ''])
            ->assertSessionHasErrors('admin_notes');

        $this->assertSame('menunggu', $permohonan->fresh()->status);
    }

    public function test_pemohon_tidak_bisa_membuka_permohonan_orang_lain(): void
    {
        $permohonan = $this->buatPermohonan('menunggu');

        $lain = User::factory()->create(['is_verified' => true, 'nip' => '199001012020011001']);
        $this->beriPermission($lain, 'Akses Pasang Baru Internet', 'User');

        $this->actingAs($lain)
            ->get(route('user.internet.pasang-baru.show', $permohonan->id))
            ->assertForbidden();
    }

    private function buatPermohonan(string $status): InternetInstallationRequest
    {
        return InternetInstallationRequest::create([
            'user_id' => $this->pemohon->id,
            'nama' => 'Pemohon', 'nip' => '1', 'no_hp' => '0',
            'lokasi_pemasangan' => 'Gedung A', 'jumlah_titik' => 1,
            'uraian_kebutuhan' => 'Kebutuhan uji', 'status' => $status,
        ]);
    }
}
