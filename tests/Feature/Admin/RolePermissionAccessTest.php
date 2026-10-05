<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Akses halaman admin harus mengikuti centang di Kelola Kewenangan,
 * bukan nama role (kasus: role Admin-Vidcon mendapat 403 walau sudah dicentang).
 */
class RolePermissionAccessTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminVidcon;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminVidcon = Role::firstOrCreate(
            ['name' => 'Admin-Vidcon'],
            ['display_name' => 'Admin-Vidcon', 'description' => 'Uji']
        );

        $this->operator = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert(['role_id' => $this->adminVidcon->id, 'user_id' => $this->operator->id]);
    }

    private function permission(string $name): Permission
    {
        return Permission::firstOrCreate(['name' => $name], ['display_name' => $name, 'group' => 'Uji']);
    }

    private function grant(string ...$names): void
    {
        foreach ($names as $name) {
            $this->adminVidcon->permissions()->syncWithoutDetaching([$this->permission($name)->id]);
        }
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert(['role_id' => Role::where('name', 'Admin')->value('id'), 'user_id' => $admin->id]);

        return $admin;
    }

    public function test_role_non_admin_tanpa_centang_ditolak(): void
    {
        $this->actingAs($this->operator)->get(route('admin.vidcon.index'))->assertForbidden();
        $this->actingAs($this->operator)->get(route('admin.vidcon-data.index'))->assertForbidden();
        $this->actingAs($this->operator)->get(route('op.tik.schedule.index'))->assertForbidden();
    }

    public function test_role_non_admin_yang_dicentang_bisa_membuka_halaman_vidcon(): void
    {
        $this->grant('admin.vidcon.index', 'admin.vidcon.data', 'admin.schedule', 'admin.statistic', 'admin.tik.assets', 'admin.tik.borrow');

        $this->actingAs($this->operator)->get(route('admin.vidcon.index'))->assertOk();
        $this->actingAs($this->operator)->get(route('admin.vidcon-data.index'))->assertOk();
        $this->actingAs($this->operator)->get(route('op.tik.schedule.index'))->assertOk();
        $this->actingAs($this->operator)->get(route('op.tik.statistic.index'))->assertOk();
        $this->actingAs($this->operator)->get(route('admin.tik.assets.index'))->assertOk();
        $this->actingAs($this->operator)->get(route('admin.tik.borrow.index'))->assertOk();

        // Centang satu halaman tidak membuka halaman lain.
        $this->actingAs($this->operator)->get(route('admin.email.index'))->assertForbidden();
    }

    public function test_halaman_inti_tetap_khusus_admin_walau_dicentang(): void
    {
        $this->grant('admin.users', 'admin.roles.index', 'admin.role-permissions');

        $this->actingAs($this->operator)->get(route('admin.users'))->assertForbidden();
        $this->actingAs($this->operator)->get(route('admin.roles.index'))->assertForbidden();
        $this->actingAs($this->operator)->get(route('admin.role-permissions'))->assertForbidden();
        $this->actingAs($this->operator)->get(route('admin.audit-logs.index'))->assertForbidden();
        $this->actingAs($this->operator)->get(route('admin.api-management.index'))->assertForbidden();
    }

    public function test_halaman_kewenangan_tampil_berurutan_sesuai_sidebar(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.role-permissions'));

        $response->assertOk()->assertSeeInOrder([
            'Kelola Permohonan', 'Layanan Digital', 'Master Data', 'Peminjaman &amp; Vidcon', 'Pengguna &amp; Akses',
        ], false);
        // Permission halaman inti tidak ditampilkan sebagai baris centang.
        $response->assertDontSee('<span class="text-xs text-gray-400 font-mono mt-0.5">admin.role-permissions</span>', false);
    }

    public function test_simpan_kewenangan_menyimpan_centang_kosong_dan_mempertahankan_permission_inti(): void
    {
        $this->grant('admin.vidcon.index', 'admin.users');
        $schedule = $this->permission('admin.schedule');
        $adminRole = Role::where('name', 'Admin')->first();
        $adminBefore = $adminRole->permissions()->pluck('permissions.id')->sort()->values()->all();

        $this->actingAs($this->admin())->post(route('admin.role-permissions.update'), [
            'permissions' => [
                $this->adminVidcon->id => (string) $schedule->id,
                $adminRole->id => implode(',', $adminBefore),
            ],
        ])->assertRedirect(route('admin.role-permissions'));

        $names = $this->adminVidcon->permissions()->pluck('name')->all();
        $this->assertContains('admin.schedule', $names);
        $this->assertNotContains('admin.vidcon.index', $names);
        // admin.users tidak tampil di form, jadi tidak boleh ikut terhapus.
        $this->assertContains('admin.users', $names);
        $this->assertSame($adminBefore, $adminRole->permissions()->pluck('permissions.id')->sort()->values()->all());

        // Semua centang dihapus (string kosong) tetap tersimpan.
        $this->actingAs($this->admin())->post(route('admin.role-permissions.update'), [
            'permissions' => [$this->adminVidcon->id => ''],
        ]);
        $this->assertSame(['admin.users'], $this->adminVidcon->permissions()->pluck('name')->all());
    }
}
