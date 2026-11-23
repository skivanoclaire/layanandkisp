<?php

namespace Tests\Feature\Email;

use App\Models\EmailRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\UnitOrganisasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmailUnitRequestTest extends TestCase
{
    use RefreshDatabase;

    private const NIP = '198501012010011001';
    private const PASSWORD = 'Rahasia!Kuat2026xyz';

    private User $pemohon;
    private UnitKerja $unitKerja;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pemohon = $this->buatPemohon(self::NIP);

        $this->unitKerja = UnitKerja::factory()->create([
            'tipe' => UnitKerja::TIPE_INDUK,
            'is_active' => true,
        ]);
    }

    /**
     * Pemohon terverifikasi dengan NIP & permission user.email.* (diberikan lewat role, seperti di UI admin).
     */
    private function buatPemohon(string $nip): User
    {
        $user = User::factory()->create([
            'is_verified' => true,
            'nip' => $nip,
        ]);

        $role = Role::firstOrCreate(['name' => 'Pemohon Email Uji'], ['display_name' => 'Pemohon Email Uji']);
        foreach (['user.email.index', 'user.email.create'] as $name) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $name, 'group' => 'Email', 'order' => 0]
            );
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $permission->id,
                'role_id' => $role->id,
            ]);
        }
        DB::table('role_user')->insert(['role_id' => $role->id, 'user_id' => $user->id]);

        return $user;
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'nama' => 'Budi Santoso',
            'no_hp' => '081234567890',
            'username' => 'dinaskominfo',
            'password' => self::PASSWORD,
            'consent_true' => '1',
        ], $extra);
    }

    private function buatPermohonanPribadiSelesai(): void
    {
        $req = new EmailRequest([
            'user_id' => $this->pemohon->id,
            'nama' => 'Budi Santoso',
            'nip' => self::NIP,
            'instansi' => $this->unitKerja->nama,
            'username' => 'budi.santoso',
            'no_hp' => '081234567890',
            'consent_true' => true,
            'status' => 'selesai',
            'submitted_at' => now(),
            'completed_at' => now(),
        ]);
        $req->setPlainPassword(self::PASSWORD);
        $req->save();
    }

    public function test_nip_yang_sudah_punya_email_pribadi_tetap_bisa_mengajukan_email_unit(): void
    {
        $this->buatPermohonanPribadiSelesai();

        $response = $this->actingAs($this->pemohon)->post(route('user.email.store'), $this->payload([
            'jenis' => EmailRequest::JENIS_UNIT_KERJA,
            'unit_kerja_id' => $this->unitKerja->id,
        ]));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('email_requests', [
            'username' => 'dinaskominfo',
            'jenis' => EmailRequest::JENIS_UNIT_KERJA,
            'unit_kerja_id' => $this->unitKerja->id,
            'nip' => self::NIP,
            'instansi' => $this->unitKerja->nama,
        ]);
    }

    public function test_nip_yang_sudah_punya_email_pribadi_ditolak_untuk_email_pribadi_kedua(): void
    {
        $this->buatPermohonanPribadiSelesai();

        $response = $this->actingAs($this->pemohon)->post(route('user.email.store'), $this->payload([
            'jenis' => EmailRequest::JENIS_PRIBADI,
            'instansi' => $this->unitKerja->nama,
        ]));

        $response->assertSessionHasErrors('nip');
        $this->assertDatabaseMissing('email_requests', ['username' => 'dinaskominfo']);
    }

    public function test_unit_organisasi_mengambil_nama_unit_dari_master_data(): void
    {
        $bidang = UnitOrganisasi::create([
            'nama' => 'Bidang Statistik',
            'tipe' => UnitOrganisasi::TIPE_BIDANG,
            'unit_kerja_id' => $this->unitKerja->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->pemohon)->post(route('user.email.store'), $this->payload([
            'jenis' => EmailRequest::JENIS_UNIT_ORGANISASI,
            'unit_organisasi_id' => $bidang->id,
            'instansi' => 'Instansi Palsu Dari Form',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('email_requests', [
            'jenis' => EmailRequest::JENIS_UNIT_ORGANISASI,
            'unit_organisasi_id' => $bidang->id,
            'instansi' => $bidang->label,
        ]);
    }

    public function test_satu_unit_hanya_boleh_punya_satu_permohonan_aktif(): void
    {
        $this->actingAs($this->pemohon)->post(route('user.email.store'), $this->payload([
            'jenis' => EmailRequest::JENIS_UNIT_KERJA,
            'unit_kerja_id' => $this->unitKerja->id,
        ]))->assertSessionHasNoErrors();

        $pemohonLain = $this->buatPemohon('199001012015011002');
        $response = $this->actingAs($pemohonLain)->post(route('user.email.store'), $this->payload([
            'jenis' => EmailRequest::JENIS_UNIT_KERJA,
            'unit_kerja_id' => $this->unitKerja->id,
            'username' => 'kominfolain',
        ]));

        $response->assertSessionHasErrors('unit_kerja_id');
        $this->assertDatabaseMissing('email_requests', ['username' => 'kominfolain']);
    }

    public function test_permohonan_unit_tidak_bisa_diedit(): void
    {
        $this->actingAs($this->pemohon)->post(route('user.email.store'), $this->payload([
            'jenis' => EmailRequest::JENIS_UNIT_KERJA,
            'unit_kerja_id' => $this->unitKerja->id,
        ]));
        $item = EmailRequest::where('jenis', EmailRequest::JENIS_UNIT_KERJA)->firstOrFail();

        $this->actingAs($this->pemohon)
            ->get(route('user.email.edit', $item->id))
            ->assertForbidden();
    }

    public function test_formulir_unit_menampilkan_pilihan_sesuai_jenis(): void
    {
        $this->actingAs($this->pemohon)
            ->get(route('user.email.unit.create', ['tipe' => 'unit_organisasi']))
            ->assertOk()
            ->assertSee('name="unit_organisasi_id"', false)
            ->assertDontSee('name="instansi"', false);
    }
}
