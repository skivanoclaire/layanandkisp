<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pemendek Tautan & layanan VPN menampilkan/menyimpan NIP pemohon, bukan NIK.
 * NIK disimpan terenkripsi di users, jadi tidak boleh tersalin polos ke kolom nip.
 */
class PemohonNipTest extends TestCase
{
    use RefreshDatabase;

    private const NIK = '6401010101900001';
    private const NIP = '198705122010011003';

    private function pemohon(array $permissions): User
    {
        $user = User::factory()->create(['is_verified' => true, 'nik' => self::NIK, 'nip' => self::NIP]);

        $roleId = DB::table('roles')->insertGetId(['name' => 'Uji NIP', 'created_at' => now(), 'updated_at' => now()]);
        foreach ($permissions as $name) {
            $permId = DB::table('permissions')->where('name', $name)->value('id')
                ?? DB::table('permissions')->insertGetId(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('permission_role')->insert(['role_id' => $roleId, 'permission_id' => $permId]);
        }
        DB::table('role_user')->insert(['role_id' => $roleId, 'user_id' => $user->id]);

        return $user;
    }

    public function test_formulir_shortlink_menampilkan_nip(): void
    {
        $user = $this->pemohon(['user.shortlink.index', 'user.shortlink.create']);

        $this->actingAs($user)->get(route('user.shortlink.create'))
            ->assertOk()
            ->assertSee(self::NIP)
            ->assertDontSee(self::NIK);
    }

    public function test_permohonan_shortlink_menyimpan_nip(): void
    {
        $user = $this->pemohon(['user.shortlink.index', 'user.shortlink.create']);

        $this->actingAs($user)->post(route('user.shortlink.store'), [
            'long_url' => 'https://kaltaraprov.go.id/halaman/panjang',
            'keperluan' => 'Uji penyimpanan NIP',
        ])->assertRedirect();

        $this->assertSame(self::NIP, DB::table('shortlink_requests')->where('user_id', $user->id)->value('nip'));
    }

    public function test_formulir_vpn_menampilkan_nip(): void
    {
        $user = $this->pemohon(['Akses Pendaftaran VPN', 'Akses Reset Akun VPN', 'Akses JIP PDNS']);

        foreach (['user.vpn.registration.create', 'user.vpn.reset.create', 'user.vpn.jip-pdns.create'] as $route) {
            $this->actingAs($user)->get(route($route))
                ->assertOk()
                ->assertSee(self::NIP)
                ->assertDontSee(self::NIK);
        }
    }
}
