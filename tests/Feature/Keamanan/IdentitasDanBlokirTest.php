<?php

namespace Tests\Feature\Keamanan;

use App\Models\Role;
use App\Models\User;
use App\Services\Sso\AkunSsoMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Perbaikan audit keamanan (A-05 / A-09 / blokir akun):
 * pencocokan SSO, perubahan NIP/NIK oleh pendaftar mandiri, dan blokir akun.
 */
class IdentitasDanBlokirTest extends TestCase
{
    use RefreshDatabase;

    private const NIP = '199001012014021001';
    private const NIK = '6471016505900001';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.simpeg.api_url' => 'https://splp.test/api_simpeg_pegawai/v1',
            'services.simpeg.api_key' => 'test-key',
        ]);
    }

    private function beriRole(User $user, string $nama): void
    {
        DB::table('role_user')->insert([
            'role_id' => Role::where('name', $nama)->value('id'),
            'user_id' => $user->id,
        ]);
    }

    public function test_pendaftar_mandiri_tidak_bisa_mengambil_alih_nip_asn(): void
    {
        $penyerang = User::factory()->create(['is_verified' => false, 'is_sso_user' => false, 'nip' => self::NIP]);

        ['user' => $cocok] = app(AkunSsoMatcher::class)->cari(self::NIP, null, null, true);

        $this->assertNull($cocok, 'Akun belum terverifikasi tidak boleh dicocokkan dengan identitas SSO.');
        $this->assertNull($penyerang->refresh()->nip, 'NIP yang diklaim akun penyerang harus dilepas.');
    }

    public function test_akun_terverifikasi_dicocokkan_lewat_nip(): void
    {
        $asn = User::factory()->create(['is_verified' => true, 'is_sso_user' => false, 'nip' => self::NIP]);

        ['user' => $cocok, 'matched_by' => $cara] = app(AkunSsoMatcher::class)->cari(self::NIP, null, null, true);

        $this->assertSame($asn->id, $cocok->id);
        $this->assertSame('nip', $cara);
    }

    public function test_nik_pendaftar_mandiri_juga_dilepas_saat_identitas_sso_dicocokkan(): void
    {
        $penyerang = User::factory()->create(['is_verified' => false, 'is_sso_user' => false, 'nik' => self::NIK]);
        $asn = User::factory()->create(['is_verified' => true, 'is_sso_user' => true, 'nip' => self::NIP, 'nik' => null]);

        app(AkunSsoMatcher::class)->cari(self::NIP, self::NIK, null, true);

        $this->assertNull($penyerang->refresh()->nik);
        $this->assertNull($penyerang->nik_hash);
        $this->assertNotNull($asn->refresh());
    }

    public function test_akun_diblokir_tidak_bisa_login_dengan_kata_sandi_benar(): void
    {
        $user = User::factory()->create(['is_verified' => true, 'email' => 'blokir@example.com']);
        $user->forceFill(['blocked_at' => now(), 'blocked_reason' => 'uji'])->save();

        $this->post('/login', ['email' => 'blokir@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_sesi_akun_yang_diblokir_dihentikan_pada_request_berikutnya(): void
    {
        $user = User::factory()->create(['is_verified' => true]);
        $this->actingAs($user);

        $user->forceFill(['blocked_at' => now()])->save();

        $this->get('/profile')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_bisa_memblokir_dan_membuka_blokir_akun(): void
    {
        $admin = User::factory()->create(['is_verified' => true]);
        $this->beriRole($admin, 'Admin');
        $target = User::factory()->create(['is_verified' => false, 'is_sso_user' => false]);

        $this->actingAs($admin)
            ->post(route('admin.users.block', $target), ['reason' => 'Upaya registrasi palsu'])
            ->assertSessionHas('success');

        $target->refresh();
        $this->assertTrue($target->isBlocked());
        $this->assertSame('Upaya registrasi palsu', $target->blocked_reason);
        $this->assertSame($admin->id, $target->blocked_by);

        $this->actingAs($admin)->post(route('admin.users.unblock', $target))->assertSessionHas('success');
        $this->assertFalse($target->refresh()->isBlocked());
    }

    public function test_admin_tidak_bisa_memblokir_akun_sendiri(): void
    {
        $admin = User::factory()->create(['is_verified' => true]);
        $this->beriRole($admin, 'Admin');

        $this->actingAs($admin)->post(route('admin.users.block', $admin))->assertSessionHas('error');
        $this->assertFalse($admin->refresh()->isBlocked());
    }

    public function test_pengguna_biasa_tidak_bisa_memblokir_akun(): void
    {
        $pengguna = User::factory()->create(['is_verified' => true]);
        $this->beriRole($pengguna, 'User');
        $target = User::factory()->create();

        $this->actingAs($pengguna)->post(route('admin.users.block', $target))->assertForbidden();
        $this->assertFalse($target->refresh()->isBlocked());
    }

    public function test_pendaftar_belum_terverifikasi_tidak_bisa_memasang_nip_tanpa_konfirmasi_simpeg(): void
    {
        Http::fake(['splp.test/*' => Http::response(['mapData' => null], 200)]);
        $user = User::factory()->create(['is_verified' => false, 'nip' => null]);

        $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'nip' => self::NIP,
            ])
            ->assertSessionHasErrors('nip');

        $this->assertNull($user->refresh()->nip);
    }

    public function test_pendaftar_belum_terverifikasi_bisa_memasang_nip_yang_dibenarkan_simpeg(): void
    {
        Http::fake(['splp.test/*' => Http::response(['mapData' => ['nip' => self::NIP, 'nama' => 'Budi']], 200)]);
        $user = User::factory()->create(['is_verified' => false, 'nip' => null]);

        $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '081234567890',
                'nip' => self::NIP,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(self::NIP, $user->refresh()->nip);
    }

    public function test_perintah_audit_identitas_berjalan_dan_tidak_mengubah_data(): void
    {
        User::factory()->create(['is_verified' => false, 'is_sso_user' => false, 'nip' => self::NIP]);

        $this->artisan('users:audit-identitas', ['--hari' => 30])->assertExitCode(0);

        $this->assertSame(self::NIP, User::where('is_verified', false)->value('nip'));
    }

    public function test_nip_yang_sudah_dipakai_akun_lain_ditolak_di_profil(): void
    {
        Http::fake(['splp.test/*' => Http::response(['mapData' => ['nip' => self::NIP]], 200)]);
        User::factory()->create(['is_verified' => true, 'nip' => self::NIP]);
        $user = User::factory()->create(['is_verified' => false, 'nip' => null]);

        $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'nip' => self::NIP,
            ])
            ->assertSessionHasErrors('nip');
    }
}
