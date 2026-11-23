<?php

namespace Tests\Feature\Email;

use App\Models\EmailAccount;
use App\Models\EmailRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\UnitOrganisasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmailUnitMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_monitoring_menampilkan_email_resmi_per_unit(): void
    {
        $admin = User::factory()->create(['is_verified' => true]);
        $role = Role::firstOrCreate(['name' => 'Admin Email Uji'], ['display_name' => 'Admin Email Uji']);
        $permission = Permission::firstOrCreate(
            ['name' => 'Kelola Master Data Email'],
            ['display_name' => 'Kelola Master Data Email', 'group' => 'Email', 'order' => 0]
        );
        DB::table('permission_role')->insertOrIgnore(['permission_id' => $permission->id, 'role_id' => $role->id]);
        DB::table('role_user')->insert(['role_id' => $role->id, 'user_id' => $admin->id]);

        $adaEmail = UnitKerja::factory()->create(['nama' => 'Dinas Kominfo Uji', 'tipe' => UnitKerja::TIPE_INDUK, 'is_active' => true]);
        $belumEmail = UnitKerja::factory()->create(['nama' => 'Dinas Belum Uji', 'tipe' => UnitKerja::TIPE_INDUK, 'is_active' => true]);

        EmailAccount::create([
            'email' => 'kominfo@kaltaraprov.go.id',
            'domain' => 'kaltaraprov.go.id',
            'user' => 'kominfo',
            'jenis' => EmailRequest::JENIS_UNIT_KERJA,
            'unit_kerja_id' => $adaEmail->id,
            'requester_instansi' => $adaEmail->nama,
        ]);

        $bidang = UnitOrganisasi::create([
            'nama' => 'Bidang Statistik Uji',
            'tipe' => UnitOrganisasi::TIPE_BIDANG,
            'unit_kerja_id' => $adaEmail->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.email-accounts.units', ['tipe' => 'unit_kerja']))
            ->assertOk()
            ->assertSee('kominfo@kaltaraprov.go.id')
            ->assertSee('Dinas Belum Uji')
            ->assertSee('Belum diajukan');

        $this->actingAs($admin)
            ->get(route('admin.email-accounts.units', ['tipe' => 'unit_organisasi']))
            ->assertOk()
            ->assertSee('Bidang Statistik Uji')
            ->assertSee($adaEmail->nama);

        $this->assertNotNull($bidang->id);
    }
}
