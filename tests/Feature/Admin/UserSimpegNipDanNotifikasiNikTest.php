<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * /admin/users: "Cek Data NIP" untuk pengguna SSO dan tombol "WA NIK" untuk
 * meminta pengguna registrasi manual memakai NIK yang terdaftar di SIMPEG.
 */
class UserSimpegNipDanNotifikasiNikTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.simpeg.api_url' => 'https://splp.test/api_simpeg_pegawai/v1',
            'services.simpeg.api_key' => 'test-key',
            'services.fonnte.tokens.aptika' => 'aptika-token',
            'services.fonnte.url' => 'https://fonnte.test/send',
        ]);

        $this->admin = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert(['role_id' => Role::where('name', 'Admin')->value('id'), 'user_id' => $this->admin->id]);
    }

    public function test_cek_data_nip_mengambil_data_simpeg_berdasarkan_nip(): void
    {
        Http::fake(['splp.test/*' => Http::response(['mapData' => [
            'nik' => '6401010101900001',
            'nip' => '198705122010011003',
            'nama' => 'Budi Santoso',
            'jabatan' => 'Pranata Komputer',
        ]], 200)]);

        $sso = User::factory()->create([
            'is_verified' => true, 'is_sso_user' => true, 'nip' => '198705122010011003', 'nik' => null,
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.simpeg.api.check-nip', $sso))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('simpeg.nama', 'Budi Santoso')
            ->assertJsonPath('simpeg.nik', '6401010101900001');

        Http::assertSent(fn ($req) => str_contains($req->url(), '/pegawai/by-nip?nip=198705122010011003')
            && $req->hasHeader('apikey', 'test-key'));
    }

    public function test_cek_data_nip_menolak_nip_bukan_18_digit_tanpa_memanggil_simpeg(): void
    {
        Http::fake();
        $sso = User::factory()->create(['is_verified' => true, 'is_sso_user' => true, 'nip' => '12345']);

        $this->actingAs($this->admin)
            ->postJson(route('admin.simpeg.api.check-nip', $sso))
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        Http::assertNothingSent();
    }

    public function test_cek_data_nip_tidak_ditemukan(): void
    {
        Http::fake(['splp.test/*' => Http::response(['ok' => false, 'valid' => false], 404)]);
        $sso = User::factory()->create(['is_verified' => true, 'is_sso_user' => true, 'nip' => '198705122010011003']);

        $this->actingAs($this->admin)
            ->postJson(route('admin.simpeg.api.check-nip', $sso))
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_apply_nik_dari_simpeg_dan_tolak_nik_milik_user_lain(): void
    {
        $sso = User::factory()->create(['is_verified' => true, 'is_sso_user' => true, 'nik' => null]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.simpeg.api.apply', $sso), ['fields' => ['nik'], 'nik' => '6401010101900001'])
            ->assertOk();
        $this->assertSame('6401010101900001', $sso->fresh()->nik);

        $lain = User::factory()->create(['is_verified' => true, 'nik' => null]);
        $this->actingAs($this->admin)
            ->postJson(route('admin.simpeg.api.apply', $lain), ['fields' => ['nik'], 'nik' => '6401010101900001'])
            ->assertStatus(409);
        $this->assertNull($lain->fresh()->nik);
    }

    public function test_notifikasi_wa_nik_dikirim_dengan_token_aptika(): void
    {
        Http::fake(['fonnte.test/*' => Http::response(['status' => true], 200)]);
        $manual = User::factory()->create(['is_verified' => true, 'is_sso_user' => false, 'phone' => '081234567890']);

        $this->actingAs($this->admin)
            ->post(route('admin.users.notify-nik', $manual))
            ->assertRedirect()
            ->assertSessionHas('status');

        Http::assertSent(fn ($req) => $req->hasHeader('Authorization', 'aptika-token')
            && $req['target'] === '081234567890'
            && str_contains($req['message'], 'SIMPEG Kaltara'));
    }

    public function test_notifikasi_wa_nik_gagal_bila_tanpa_nomor_hp(): void
    {
        Http::fake();
        $manual = User::factory()->create(['is_verified' => true, 'phone' => '']);

        $this->actingAs($this->admin)
            ->post(route('admin.users.notify-nik', $manual))
            ->assertSessionHas('error');

        Http::assertNothingSent();
    }

    public function test_halaman_users_menampilkan_tombol_baru(): void
    {
        User::factory()->create(['is_verified' => true, 'is_sso_user' => true, 'nip' => '198705122010011003']);
        User::factory()->create(['is_verified' => true, 'is_sso_user' => false, 'phone' => '081234567890']);

        $this->actingAs($this->admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Cek Data NIP')
            ->assertSee('WA NIK');
    }
}
