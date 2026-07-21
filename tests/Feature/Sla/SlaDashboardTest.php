<?php

namespace Tests\Feature\Sla;

use App\Models\SlaSetting;
use App\Models\User;
use App\Services\Sla\SlaCalculationService;
use App\Services\Sla\SlaServiceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SlaDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'Admin']);

        // Permission "Manajemen SLA" sudah diberikan ke role Admin oleh
        // 2026_07_20_000004_create_sla_permissions; user tinggal dikaitkan ke role itu.
        $roleId = DB::table('roles')->where('name', 'Admin')->value('id');
        DB::table('role_user')->insertOrIgnore([
            'role_id' => $roleId,
            'user_id' => $this->admin->id,
        ]);
    }

    private function seedVidcon(string $status, Carbon $submittedAt, ?Carbon $completedAt = null): void
    {
        DB::table('vidcon_requests')->insert([
            'ticket_no' => 'TKT-VC-'.uniqid(),
            'user_id' => $this->admin->id,
            'nama' => $this->admin->name,
            'email_pemohon' => $this->admin->email,
            'judul_kegiatan' => 'Rapat Uji SLA',
            'tanggal_mulai' => $submittedAt->toDateString(),
            'tanggal_selesai' => $submittedAt->toDateString(),
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '11:00:00',
            'status' => $status,
            'submitted_at' => $submittedAt,
            'completed_at' => $completedAt,
            'created_at' => $submittedAt,
            'updated_at' => $completedAt ?? $submittedAt,
        ]);
    }

    /**
     * Hari kerja (Senin) pukul $hour, di dalam bulan berjalan.
     */
    private function workingDayAt(int $hour): Carbon
    {
        $day = Carbon::now()->startOfMonth();
        while (! in_array($day->isoWeekday(), [1, 2, 3, 4, 5], true)) {
            $day->addDay();
        }

        return $day->setTime($hour, 0);
    }

    public function test_dashboard_sla_dapat_diakses_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.sla.index'))
            ->assertOk()
            ->assertSee('Dashboard Capaian SLA');
    }

    public function test_dashboard_menolak_user_tanpa_permission(): void
    {
        $user = User::factory()->create(['role' => 'User']);

        $this->actingAs($user)
            ->get(route('admin.sla.index'))
            ->assertForbidden();
    }

    public function test_halaman_pengaturan_mengisi_target_default_seluruh_layanan(): void
    {
        $this->assertSame(0, SlaSetting::count());

        $this->actingAs($this->admin)
            ->get(route('admin.sla.pengaturan'))
            ->assertOk();

        $this->assertSame(count(SlaServiceRegistry::all()), SlaSetting::count());
    }

    public function test_service_key_tidak_dikenal_menghasilkan_404(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.sla.show', 'layanan-palsu'))
            ->assertNotFound();
    }

    /**
     * Regresi: detail() sempat menampilkan baris berstatus draft yang tidak dihitung
     * summary(), sehingga jumlah di dashboard dan halaman detail tidak cocok dan
     * baris draft dirender tanpa properti bucket/overdue.
     */
    public function test_summary_dan_detail_konsisten_untuk_layanan_berstatus_draft(): void
    {
        SlaSetting::ensureDefaults();

        foreach (['draft', 'draft', 'diajukan', 'disetujui'] as $status) {
            DB::table('rekomendasi_aplikasi_forms')->insert([
                'ticket_number' => 'TKT-'.uniqid(),
                'user_id' => $this->admin->id,
                'jenis_layanan' => 'internal',
                'status' => $status,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $sla = app(SlaCalculationService::class);
        $summary = $sla->summaryFor('rekomendasi_usulan');
        $detail = $sla->detail('rekomendasi_usulan');

        // Hanya 2 baris non-draft yang boleh terhitung, di kedua sisi.
        $this->assertSame(2, $summary['total']);
        $this->assertSame(2, $detail->total());

        foreach ($detail as $row) {
            $this->assertObjectHasProperty('bucket', $row);
            $this->assertNotSame('draft', $row->status);
        }
    }

    /**
     * Regresi: kolom is_active pada sla_settings sebelumnya diabaikan, sehingga
     * menonaktifkan target tidak berpengaruh pada perhitungan capaian.
     */
    public function test_target_nonaktif_tidak_menghitung_capaian(): void
    {
        SlaSetting::ensureDefaults();

        // Selesai dalam 1 jam kerja — jauh di bawah target, jadi pasti "tercapai".
        $this->seedVidcon('selesai', $this->workingDayAt(9), $this->workingDayAt(10));

        $sla = app(SlaCalculationService::class);

        $aktif = $sla->summaryFor('vidcon');
        $this->assertTrue($aktif['target_active']);
        $this->assertSame(1, $aktif['achieved']);
        $this->assertSame(100.0, $aktif['achieved_pct']);

        SlaSetting::where('service_key', 'vidcon')->update(['is_active' => false]);

        $nonaktif = $sla->summaryFor('vidcon');
        $this->assertFalse($nonaktif['target_active']);
        $this->assertSame(0, $nonaktif['achieved'] + $nonaktif['breached']);
        $this->assertNull($nonaktif['achieved_pct']);
        // Permohonannya tetap tercatat, hanya capaian SLA-nya yang tidak dinilai.
        $this->assertSame(1, $nonaktif['total']);
    }

    public function test_permohonan_melewati_target_dihitung_terlambat(): void
    {
        SlaSetting::ensureDefaults();
        SlaSetting::where('service_key', 'vidcon')
            ->update(['target_value' => 1, 'target_unit' => 'jam']);

        // Selesai setelah 4 jam kerja, target hanya 1 jam.
        $this->seedVidcon('selesai', $this->workingDayAt(9), $this->workingDayAt(13));

        $summary = app(SlaCalculationService::class)->summaryFor('vidcon');

        $this->assertSame(0, $summary['achieved']);
        $this->assertSame(1, $summary['breached']);
        $this->assertSame(0.0, $summary['achieved_pct']);
    }

    public function test_update_target_sla_tersimpan(): void
    {
        SlaSetting::ensureDefaults();

        $this->actingAs($this->admin)
            ->put(route('admin.sla.pengaturan.update', 'vidcon'), [
                'target_value' => 12,
                'target_unit' => 'jam',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.sla.pengaturan'));

        $setting = SlaSetting::where('service_key', 'vidcon')->first();
        $this->assertSame(12, $setting->target_value);
        $this->assertSame('jam', $setting->target_unit);
        $this->assertTrue($setting->is_active);
    }

    public function test_jam_selesai_harus_setelah_jam_mulai(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.sla.pengaturan.jam-kerja'), [
                'jam_mulai' => '16:00',
                'jam_selesai' => '08:00',
                'hari_kerja' => [1, 2, 3, 4, 5],
            ])
            ->assertSessionHasErrors('jam_selesai');
    }

    public function test_tanggal_libur_tidak_boleh_ganda(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.sla.pengaturan.libur.store'), [
                'tanggal' => '2026-08-17',
                'keterangan' => 'HUT RI',
            ])
            ->assertRedirect(route('admin.sla.pengaturan'));

        $this->actingAs($this->admin)
            ->post(route('admin.sla.pengaturan.libur.store'), [
                'tanggal' => '2026-08-17',
                'keterangan' => 'Duplikat',
            ])
            ->assertSessionHasErrors('tanggal');

        $this->assertSame(1, DB::table('sla_holidays')->count());
    }
}
