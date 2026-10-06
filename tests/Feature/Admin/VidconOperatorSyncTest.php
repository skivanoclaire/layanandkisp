<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\VidconData;
use App\Models\VidconRequest;
use App\Models\VidconRequestActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Operator yang ditugaskan pada permohonan vidcon yang sudah selesai bisa direvisi,
 * dan halaman Permohonan Vidcon serta Master Data Vidcon saling memperbarui.
 */
class VidconOperatorSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $opA;
    private User $opB;
    private VidconRequest $item;
    private VidconData $vidconData;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();

        $this->admin = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert(['role_id' => Role::where('name', 'Admin')->value('id'), 'user_id' => $this->admin->id]);

        $operatorRole = Role::firstOrCreate(
            ['name' => 'Operator-Vidcon'],
            ['display_name' => 'Operator-Vidcon', 'description' => 'Uji']
        );
        $this->opA = User::factory()->create(['is_verified' => true, 'name' => 'Operator A']);
        $this->opB = User::factory()->create(['is_verified' => true, 'name' => 'Operator B']);
        foreach ([$this->opA, $this->opB] as $op) {
            DB::table('role_user')->insert(['role_id' => $operatorRole->id, 'user_id' => $op->id]);
        }

        $this->item = VidconRequest::create([
            'ticket_no' => 'VC-UJI-0001',
            'user_id' => $this->admin->id,
            'nama' => 'Pemohon Uji',
            'email_pemohon' => 'pemohon@example.com',
            'no_hp' => '081234567890',
            'judul_kegiatan' => 'Rapat Uji',
            'tanggal_mulai' => '2026-10-10',
            'tanggal_selesai' => '2026-10-10',
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'status' => 'selesai',
            'link_meeting' => 'https://zoom.us/j/111',
            'meeting_id' => '111',
            'meeting_password' => 'abc',
            'akun_zoom' => '001',
        ]);
        $this->item->operators()->sync([$this->opA->id]);

        $this->vidconData = VidconData::create([
            'vidcon_request_id' => $this->item->id,
            'nama_pemohon' => 'Pemohon Uji',
            'judul_kegiatan' => 'Rapat Uji',
            'tanggal_mulai' => '2026-10-10',
            'tanggal_selesai' => '2026-10-10',
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'platform' => 'Zoom',
            'link_meeting' => 'https://zoom.us/j/111',
            'meeting_id' => '111',
            'meeting_password' => 'abc',
            'akun_zoom' => '001',
            'operator' => 'Operator A',
        ]);
        $this->vidconData->operators()->sync([$this->opA->id]);
    }

    public function test_halaman_permohonan_selesai_menampilkan_pilihan_operator_pada_revisi(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.vidcon.show', $this->item->id))
            ->assertOk()
            ->assertSee('Revisi Informasi Meeting &amp; Operator', false)
            ->assertSee('Operator B');
    }

    public function test_revisi_operator_dari_permohonan_ikut_mengubah_master_data(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.vidcon.revise', $this->item->id), [
                'link_meeting' => 'https://zoom.us/j/111',
                'meeting_id' => '111',
                'meeting_password' => 'abc',
                'akun_zoom' => '001',
                'operators' => [$this->opB->id],
            ])
            ->assertRedirect(route('admin.vidcon.show', $this->item->id))
            ->assertSessionHas('success');

        $this->assertSame([$this->opB->id], $this->item->operators()->pluck('users.id')->all());
        $this->assertSame([$this->opB->id], $this->vidconData->operators()->pluck('users.id')->all());
        $this->assertSame('Operator B', $this->vidconData->fresh()->operator);

        // Hanya operator yang berubah: pemohon tidak perlu dikirimi WhatsApp.
        Http::assertNothingSent();
    }

    public function test_ubah_master_data_ikut_mengubah_permohonan(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.vidcon-data.update', $this->vidconData), [
                'nama_pemohon' => 'Pemohon Uji',
                'nip_pemohon' => '1990',
                'email_pemohon' => 'pemohon@example.com',
                'unit_kerja_id' => \App\Models\UnitKerja::factory()->create()->id,
                'no_hp' => '081234567890',
                'judul_kegiatan' => 'Rapat Uji',
                'tanggal_mulai' => '2026-10-10',
                'tanggal_selesai' => '2026-10-10',
                'jam_mulai' => '09:00',
                'jam_selesai' => '11:00',
                'platform' => 'Zoom',
                'link_meeting' => 'https://zoom.us/j/222',
                'meeting_id' => '222',
                'meeting_password' => 'xyz',
                'akun_zoom' => '002',
                'operators' => [$this->opA->id, $this->opB->id],
            ])
            ->assertRedirect(route('admin.vidcon-data.index'));

        $item = $this->item->fresh();
        $this->assertSame('https://zoom.us/j/222', $item->link_meeting);
        $this->assertSame('222', $item->meeting_id);
        $this->assertSame('002', $item->akun_zoom);
        $this->assertEqualsCanonicalizing(
            [$this->opA->id, $this->opB->id],
            $item->operators()->pluck('users.id')->all()
        );
        $this->assertTrue(
            VidconRequestActivity::where('vidcon_request_id', $item->id)
                ->where('notes', 'like', 'Diperbarui dari Master Data Vidcon%')
                ->exists()
        );
    }
}
