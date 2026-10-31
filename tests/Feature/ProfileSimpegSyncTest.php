<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * /profile "Sinkron Data via SIMPEG": cek berdasarkan NIK atau NIP yang tersimpan.
 * Pengguna SSO umumnya hanya menyimpan NIP.
 */
class ProfileSimpegSyncTest extends TestCase
{
    use RefreshDatabase;

    private const NIK = '6401010101900001';
    private const NIP = '198705122010011003';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.simpeg.api_url' => 'https://splp.test/api_simpeg_pegawai/v1',
            'services.simpeg.api_key' => 'test-key',
        ]);
    }

    private function fakeFound(): void
    {
        Http::fake(['splp.test/*' => Http::response(['mapData' => [
            'nik' => self::NIK, 'nip' => self::NIP, 'nama' => 'Budi Santoso',
        ]], 200)]);
    }

    public function test_user_sso_tanpa_nik_dicek_berdasarkan_nip(): void
    {
        $this->fakeFound();
        $user = User::factory()->create(['is_verified' => true, 'nik' => null, 'nip' => self::NIP]);

        $this->actingAs($user)
            ->postJson(route('profile.simpeg.check'))
            ->assertOk()
            ->assertJsonPath('matched_by', 'nip')
            ->assertJsonPath('simpeg.nik', self::NIK)
            ->assertJsonPath('editable.nik', true);

        Http::assertSent(fn ($req) => str_contains($req->url(), '/pegawai/by-nip?nip=' . self::NIP));
        Http::assertNotSent(fn ($req) => str_contains($req->url(), '/pegawai/by-nik'));
    }

    public function test_nik_lebih_dulu_lalu_fallback_ke_nip_jika_tidak_ditemukan(): void
    {
        Http::fake([
            'splp.test/*/pegawai/by-nik*' => Http::response(['ok' => false, 'valid' => false], 404),
            'splp.test/*/pegawai/by-nip*' => Http::response(['mapData' => ['nip' => self::NIP, 'nama' => 'Budi']], 200),
        ]);
        $user = User::factory()->create(['nik' => '6401010101900099', 'nip' => self::NIP]);

        $this->actingAs($user)
            ->postJson(route('profile.simpeg.check'))
            ->assertOk()
            ->assertJsonPath('matched_by', 'nip');

        Http::assertSentCount(2);
    }

    public function test_ditolak_tanpa_memanggil_simpeg_jika_nik_dan_nip_kosong(): void
    {
        Http::fake();
        $user = User::factory()->create(['nik' => null, 'nip' => null]);

        $this->actingAs($user)
            ->postJson(route('profile.simpeg.check'))
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        Http::assertNothingSent();
    }

    public function test_user_terverifikasi_dapat_melengkapi_nik_kosong_dari_simpeg(): void
    {
        $this->fakeFound();
        $user = User::factory()->create(['is_verified' => true, 'nik' => null, 'nip' => self::NIP]);

        $this->actingAs($user)->postJson(route('profile.simpeg.check'))->assertOk();

        // NIK dari client diabaikan; yang dipakai NIK hasil cek SIMPEG.
        $this->actingAs($user)
            ->postJson(route('profile.simpeg.apply'), ['fields' => ['nik'], 'nik' => '6401010101909999'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(self::NIK, $user->fresh()->nik);
    }

    public function test_nik_simpeg_milik_akun_lain_ditolak(): void
    {
        $this->fakeFound();
        User::factory()->create(['nik' => self::NIK]);
        $user = User::factory()->create(['is_verified' => true, 'nik' => null, 'nip' => self::NIP]);

        $this->actingAs($user)->postJson(route('profile.simpeg.check'))->assertOk();
        $this->actingAs($user)
            ->postJson(route('profile.simpeg.apply'), ['fields' => ['nik']])
            ->assertStatus(409);

        $this->assertNull($user->fresh()->nik);
    }

    public function test_nik_yang_sudah_ada_tetap_terkunci_untuk_user_terverifikasi(): void
    {
        $this->fakeFound();
        $user = User::factory()->create(['is_verified' => true, 'nik' => '6401010101900099', 'nip' => self::NIP]);

        $this->actingAs($user)
            ->postJson(route('profile.simpeg.check'))
            ->assertOk()
            ->assertJsonPath('editable.nik', false);

        $this->actingAs($user)->postJson(route('profile.simpeg.apply'), ['fields' => ['nik']])->assertOk();

        $this->assertSame('6401010101900099', $user->fresh()->nik);
    }
}
