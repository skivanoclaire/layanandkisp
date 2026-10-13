<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SsoLoginTest extends TestCase
{
    use RefreshDatabase;

    private const NIP = '199001012015031001';

    private function loginSso(string $nip, string $email, array $raw = []): \Illuminate\Testing\TestResponse
    {
        $ssoUser = (new SocialiteUser())->setRaw($raw)->map([
            'nickname' => $nip,
            'email' => $email,
            'name' => 'Pegawai SSO',
        ]);

        Socialite::shouldReceive('driver->user')->andReturn($ssoUser);

        return $this->get('/auth/keycloak/callback');
    }

    public function test_nip_yang_sudah_ada_masuk_ke_akun_lama_walau_email_sso_berbeda(): void
    {
        $lama = User::factory()->create([
            'nip' => self::NIP,
            'email' => 'lama@example.com',
            'name' => 'Nama Lengkap Lama',
            'is_verified' => true,
        ]);

        $this->loginSso(self::NIP, 'baru@example.com')->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($lama);
        // Nama di SSO bisa tidak lengkap; nama akun lama dipertahankan.
        $this->assertSame('Nama Lengkap Lama', $lama->fresh()->name);
        $this->assertSame(1, User::where('nip', self::NIP)->count());
        $this->assertDatabaseMissing('users', ['email' => 'baru@example.com']);
        // Email akun lama adalah kredensial login lokal, tidak boleh ditimpa SSO.
        $this->assertSame('lama@example.com', $lama->fresh()->email);
    }

    public function test_nip_lebih_diutamakan_daripada_email_yang_dipakai_akun_lain(): void
    {
        $pemilikNip = User::factory()->create(['nip' => self::NIP, 'is_verified' => true]);
        $pemilikEmail = User::factory()->create(['email' => 'baru@example.com', 'is_verified' => true]);

        $this->loginSso(self::NIP, 'baru@example.com')->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($pemilikNip);
        $this->assertNull($pemilikEmail->fresh()->nip);
    }

    public function test_nip_yang_terlanjur_ganda_masuk_ke_akun_lama_bukan_akun_buatan_sso(): void
    {
        $duplikat = User::factory()->create([
            'nip' => self::NIP,
            'email' => 'baru@example.com',
            'is_sso_user' => true,
            'is_verified' => true,
        ]);
        $lama = User::factory()->create([
            'nip' => self::NIP,
            'email' => 'lama@example.com',
            'is_verified' => true,
        ]);

        $this->loginSso(self::NIP, 'baru@example.com')->assertRedirect('/dashboard');

        $this->assertTrue($duplikat->id < $lama->id);
        $this->assertAuthenticatedAs($lama);
    }

    public function test_akun_tanpa_nip_dicocokkan_lewat_email_lalu_nip_diisi(): void
    {
        $user = User::factory()->create(['email' => 'pegawai@example.com', 'is_verified' => true]);

        $this->loginSso(self::NIP, 'pegawai@example.com')->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->assertSame(self::NIP, $user->fresh()->nip);
    }

    public function test_asn_yang_belum_punya_akun_dibuatkan_akun_baru(): void
    {
        $this->loginSso(self::NIP, 'baru@example.com')->assertRedirect('/dashboard');

        $user = User::where('nip', self::NIP)->sole();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('baru@example.com', $user->email);
        $this->assertTrue($user->is_sso_user);
        $this->assertTrue($user->is_verified);
    }
}
