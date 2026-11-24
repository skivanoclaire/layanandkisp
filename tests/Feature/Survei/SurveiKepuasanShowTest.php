<?php

namespace Tests\Feature\Survei;

use App\Models\Role;
use App\Models\SurveiKepuasanLayanan;
use App\Models\User;
use App\Models\WebMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Halaman detail survei kepuasan admin (sebelumnya 500: view admin.survei-kepuasan.show tidak ada). */
class SurveiKepuasanShowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert(['role_id' => Role::where('name', 'Admin')->value('id'), 'user_id' => $admin->id]);

        return $admin;
    }

    public function test_detail_survei_tampil_untuk_admin(): void
    {
        $responden = User::factory()->create(['name' => 'Responden Uji']);
        $monitor = WebMonitor::create(['nama_sistem' => 'Sistem Uji Survei', 'subdomain' => 'uji-survei']);
        $survei = SurveiKepuasanLayanan::create([
            'user_id' => $responden->id,
            'web_monitor_id' => $monitor->id,
            'rating_kecepatan' => 4, 'rating_kemudahan' => 5, 'rating_kualitas' => 3,
            'rating_responsif' => 4, 'rating_keamanan' => 5, 'rating_keseluruhan' => 4,
            'saran' => 'Tambahkan panduan', 'kelebihan' => 'Cepat', 'kekurangan' => 'Lambat',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.survei-kepuasan.show', $survei->id))
            ->assertOk()
            ->assertSee('Responden Uji')
            ->assertSee('Sistem Uji Survei')
            ->assertSee('Tambahkan panduan')
            ->assertSee('Kecepatan');
    }
}
