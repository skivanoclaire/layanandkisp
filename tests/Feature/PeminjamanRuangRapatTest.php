<?php

namespace Tests\Feature;

use App\Models\MeetingRoom;
use App\Models\MeetingRoomBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PeminjamanRuangRapatTest extends TestCase
{
    use RefreshDatabase;

    private User $pemohon;
    private User $admin;
    private MeetingRoom $ruang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pemohon = User::factory()->create(['is_verified' => true, 'nip' => '199001012020011001']);
        $this->admin = User::factory()->create(['is_verified' => true, 'nip' => '199001012020011001']);
        $this->beriPermission($this->pemohon, 'Akses Peminjaman Ruang Rapat', 'User');
        $this->beriPermission($this->admin, 'Kelola Peminjaman Ruang Rapat', 'Admin');

        $this->ruang = MeetingRoom::create([
            'nama' => 'Ruang Rapat Utama',
            'lokasi' => 'Lantai 2',
            'kapasitas' => 20,
            'is_active' => true,
        ]);
    }

    private function beriPermission(User $user, string $permission, string $role): void
    {
        $roleId = DB::table('roles')->where('name', $role)->value('id')
            ?? DB::table('roles')->insertGetId(['name' => $role, 'display_name' => $role, 'created_at' => now(), 'updated_at' => now()]);

        $permId = DB::table('permissions')->where('name', $permission)->value('id')
            ?? DB::table('permissions')->insertGetId([
                'name' => $permission, 'display_name' => $permission, 'group' => 'Test', 'order' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);

        DB::table('permission_role')->insertOrIgnore([
            'role_id' => $roleId, 'permission_id' => $permId, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('role_user')->insertOrIgnore(['role_id' => $roleId, 'user_id' => $user->id]);
    }

    private function dataPermohonan(array $override = []): array
    {
        return array_merge([
            'meeting_room_id' => $this->ruang->id,
            'no_hp' => '081234567890',
            'keperluan' => 'Rapat koordinasi',
            'jumlah_peserta' => 10,
            'tanggal' => now()->addDays(3)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
        ], $override);
    }

    public function test_pemohon_bisa_mengajukan_peminjaman_berstatus_menunggu(): void
    {
        $this->actingAs($this->pemohon)
            ->post(route('user.peminjaman-ruang-rapat.store'), $this->dataPermohonan())
            ->assertRedirect(route('user.peminjaman-ruang-rapat.index'))
            ->assertSessionHasNoErrors();

        $booking = MeetingRoomBooking::first();
        $this->assertNotNull($booking);
        $this->assertSame('menunggu', $booking->status);
        $this->assertStringStartsWith('RR-', $booking->ticket_no);
        $this->assertSame($this->pemohon->id, $booking->user_id);
    }

    public function test_peminjaman_ditolak_bila_melebihi_kapasitas(): void
    {
        $this->actingAs($this->pemohon)
            ->post(route('user.peminjaman-ruang-rapat.store'), $this->dataPermohonan(['jumlah_peserta' => 25]))
            ->assertSessionHasErrors('jumlah_peserta');

        $this->assertSame(0, MeetingRoomBooking::count());
    }

    public function test_peminjaman_ditolak_bila_jam_beririsan_dengan_yang_sudah_disetujui(): void
    {
        MeetingRoomBooking::create([
            'user_id' => $this->admin->id,
            'meeting_room_id' => $this->ruang->id,
            'nama' => 'Lain', 'nip' => '1', 'no_hp' => '0',
            'keperluan' => 'Rapat lain', 'jumlah_peserta' => 5,
            'tanggal' => now()->addDays(3)->format('Y-m-d'),
            'jam_mulai' => '10:00', 'jam_selesai' => '12:00',
            'status' => 'disetujui',
        ]);

        $this->actingAs($this->pemohon)
            ->post(route('user.peminjaman-ruang-rapat.store'), $this->dataPermohonan())
            ->assertSessionHasErrors('jam_mulai');

        $this->assertSame(1, MeetingRoomBooking::count());
    }

    public function test_admin_tidak_bisa_menyetujui_dua_permohonan_yang_beririsan(): void
    {
        $tanggal = now()->addDays(3)->format('Y-m-d');

        $pertama = $this->buatPermohonan($tanggal, '09:00', '11:00');
        $kedua = $this->buatPermohonan($tanggal, '10:00', '12:00');

        $this->actingAs($this->admin)
            ->post(route('admin.peminjaman-ruang-rapat.approve', $pertama->id))
            ->assertSessionHas('success');

        $this->actingAs($this->admin)
            ->post(route('admin.peminjaman-ruang-rapat.approve', $kedua->id))
            ->assertSessionHas('error');

        $this->assertSame('disetujui', $pertama->fresh()->status);
        $this->assertSame('menunggu', $kedua->fresh()->status);
    }

    public function test_admin_bisa_menolak_dengan_alasan(): void
    {
        $permohonan = $this->buatPermohonan(now()->addDays(3)->format('Y-m-d'), '09:00', '10:00');

        $this->actingAs($this->admin)
            ->post(route('admin.peminjaman-ruang-rapat.reject', $permohonan->id), ['admin_notes' => 'Ruang dipakai internal'])
            ->assertSessionHas('success');

        $this->assertSame('ditolak', $permohonan->fresh()->status);
        $this->assertSame('Ruang dipakai internal', $permohonan->fresh()->admin_notes);
    }

    public function test_pemohon_hanya_bisa_membatalkan_permohonan_yang_masih_menunggu(): void
    {
        $permohonan = $this->buatPermohonan(now()->addDays(3)->format('Y-m-d'), '09:00', '10:00');

        $this->actingAs($this->pemohon)
            ->post(route('user.peminjaman-ruang-rapat.cancel', $permohonan->id))
            ->assertSessionHas('success');

        $this->assertSame('dibatalkan', $permohonan->fresh()->status);
    }

    public function test_pemohon_tidak_bisa_membuka_permohonan_orang_lain(): void
    {
        $permohonan = $this->buatPermohonan(now()->addDays(3)->format('Y-m-d'), '09:00', '10:00');

        $lain = User::factory()->create(['is_verified' => true, 'nip' => '199001012020011001']);
        $this->beriPermission($lain, 'Akses Peminjaman Ruang Rapat', 'User');

        $this->actingAs($lain)
            ->get(route('user.peminjaman-ruang-rapat.show', $permohonan->id))
            ->assertForbidden();
    }

    private function buatPermohonan(string $tanggal, string $mulai, string $selesai): MeetingRoomBooking
    {
        return MeetingRoomBooking::create([
            'user_id' => $this->pemohon->id,
            'meeting_room_id' => $this->ruang->id,
            'nama' => 'Pemohon', 'nip' => '1', 'no_hp' => '0',
            'keperluan' => 'Rapat', 'jumlah_peserta' => 5,
            'tanggal' => $tanggal, 'jam_mulai' => $mulai, 'jam_selesai' => $selesai,
            'status' => 'menunggu',
        ]);
    }
}
