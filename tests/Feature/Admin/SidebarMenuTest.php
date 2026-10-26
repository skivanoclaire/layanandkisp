<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminPendingCountsService;
use App\Services\SidebarMenuService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Sidebar, navbar, dan dashboard dibangun dari SidebarMenuService.
 * Test ini memastikan setiap route di menu valid dan menu mengikuti permission.
 */
class SidebarMenuTest extends TestCase
{
    use RefreshDatabase;

    private int $roleSeq = 0;

    /** Semua nama permission yang dirujuk SidebarMenuService. */
    private function menuPermissions(): array
    {
        $source = file_get_contents(app_path('Services/SidebarMenuService.php'));
        preg_match_all('/has(?:Any)?Permission\((\[[^\]]*\]|\'[^\']*\')\)/', $source, $calls);

        $names = [];
        foreach ($calls[1] as $arg) {
            preg_match_all("/'([^']+)'/", $arg, $quoted);
            array_push($names, ...$quoted[1]);
        }

        return array_values(array_unique($names));
    }

    private function userWith(array $permissions, bool $verified = true, ?string $roleName = null): User
    {
        $user = User::factory()->create(['is_verified' => $verified]);

        $role = $roleName
            ? Role::firstOrCreate(['name' => $roleName], ['display_name' => $roleName, 'description' => 'Uji'])
            : Role::create(['name' => 'Uji-' . ++$this->roleSeq, 'display_name' => 'Uji', 'description' => 'Uji']);

        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name], ['display_name' => $name, 'group' => 'Uji']);
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }
        DB::table('role_user')->insert(['role_id' => $role->id, 'user_id' => $user->id]);

        return $user;
    }

    public function test_admin_dengan_semua_permission_melihat_seluruh_menu(): void
    {
        $admin = $this->userWith($this->menuPermissions(), true, 'Admin');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Kelola Permohonan')
            ->assertSee('Survei &amp; Evaluasi', false)
            ->assertSee('Layanan Digital')
            ->assertSee('Master Data')
            ->assertSee('Peminjaman &amp; Vidcon', false)
            ->assertSee('Pengaturan Sistem')
            ->assertSee('Perlu Tindakan')
            ->assertSee(route('admin.dtsen.laporan.insiden'), false)
            ->assertSee(route('admin.api-management.index'), false);

        $this->actingAs($admin)->get(route('user.dashboard'))->assertOk()->assertSee('Ajukan Layanan');
    }

    public function test_pengguna_biasa_hanya_melihat_layanan_miliknya(): void
    {
        $user = $this->userWith(['user.dashboard', 'Akses SPLP']);

        $response = $this->actingAs($user)->get(route('user.dashboard'));

        $response->assertOk()
            ->assertSee('Layanan Digital')
            ->assertSee('Integrasi (SPLP)')
            ->assertSee(route('user.splp.provider.index'), false)
            ->assertDontSee('Kelola Permohonan')
            ->assertDontSee('Master Data')
            ->assertDontSee('Permohonan Menunggu');
    }

    public function test_pengguna_belum_terverifikasi_tidak_melihat_layanan_digital(): void
    {
        $user = $this->userWith(['user.dashboard', 'Akses SPLP', 'Akses Survei Kepuasan'], verified: false);

        $response = $this->actingAs($user)->get(route('user.dashboard'));

        $response->assertOk()
            ->assertSee('Akun belum diverifikasi')
            ->assertDontSee(route('user.splp.provider.index'), false)
            ->assertDontSee(route('survei-kepuasan.index'), false);
    }

    public function test_permohonan_menunggu_tampil_di_notifikasi_dan_dashboard(): void
    {
        $this->app->instance(AdminPendingCountsService::class, new class extends AdminPendingCountsService {
            public function countsFor(?User $user): array
            {
                return ['email_request' => 3, 'email_password_reset' => 0, 'email' => 3, 'total' => 3];
            }
        });

        $admin = $this->userWith(['admin.dashboard', 'admin.email']);

        $menu = $this->actingAs($admin)->app->make(SidebarMenuService::class);
        $this->assertSame(
            [['label' => 'Permohonan', 'group' => 'Email', 'url' => route('admin.email.index'), 'icon' => 'mail', 'badge' => 3]],
            $menu->pendingItems()
        );

        $this->app->forgetScopedInstances();
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Permohonan Menunggu')
            ->assertSee('3 permohonan');
    }
}
