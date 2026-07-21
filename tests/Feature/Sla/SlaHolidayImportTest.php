<?php

namespace Tests\Feature\Sla;

use App\Models\SlaHoliday;
use App\Models\User;
use App\Services\Sla\NationalHolidayImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class SlaHolidayImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'Admin']);

        $roleId = DB::table('roles')->where('name', 'Admin')->value('id');
        DB::table('role_user')->insertOrIgnore([
            'role_id' => $roleId,
            'user_id' => $this->admin->id,
        ]);
    }

    private function fakeApi(array $rows): void
    {
        Http::fake([
            '*' => Http::response($rows, 200),
        ]);
    }

    public function test_impor_menyimpan_libur_dan_menandai_cuti_bersama(): void
    {
        $this->fakeApi([
            ['date' => '2026-08-17', 'name' => 'Hari Kemerdekaan RI', 'is_national_holiday' => true],
            ['date' => '2026-12-24', 'name' => 'Cuti Bersama Natal', 'is_national_holiday' => false],
        ]);

        $hasil = app(NationalHolidayImporter::class)->import(2026);

        $this->assertSame(2, $hasil['ditambah']);
        $this->assertDatabaseHas('sla_holidays', [
            'tanggal' => '2026-08-17',
            'jenis' => SlaHoliday::JENIS_LIBUR_NASIONAL,
            'sumber' => SlaHoliday::SUMBER_IMPORT,
        ]);
        $this->assertDatabaseHas('sla_holidays', [
            'tanggal' => '2026-12-24',
            'jenis' => SlaHoliday::JENIS_CUTI_BERSAMA,
        ]);
    }

    public function test_entri_manual_tidak_ditimpa_oleh_impor(): void
    {
        SlaHoliday::create([
            'tanggal' => '2026-08-17',
            'keterangan' => 'Catatan admin sendiri',
            'sumber' => SlaHoliday::SUMBER_MANUAL,
        ]);

        $this->fakeApi([
            ['date' => '2026-08-17', 'name' => 'Hari Kemerdekaan RI', 'is_national_holiday' => true],
        ]);

        $hasil = app(NationalHolidayImporter::class)->import(2026);

        $this->assertSame(1, $hasil['dilewati']);
        $this->assertSame(0, $hasil['ditambah']);
        $this->assertDatabaseHas('sla_holidays', [
            'tanggal' => '2026-08-17',
            'keterangan' => 'Catatan admin sendiri',
        ]);
    }

    public function test_tanggal_impor_yang_hilang_dari_api_dihapus_tapi_manual_bertahan(): void
    {
        // Cuti bersama yang dibatalkan lewat revisi SKB.
        SlaHoliday::create([
            'tanggal' => '2026-05-15',
            'keterangan' => 'Cuti Bersama Kenaikan Yesus Kristus',
            'sumber' => SlaHoliday::SUMBER_IMPORT,
        ]);
        // Libur daerah — tidak akan pernah ada di API.
        SlaHoliday::create([
            'tanggal' => '2026-04-15',
            'keterangan' => 'HUT Provinsi Kaltara',
            'sumber' => SlaHoliday::SUMBER_MANUAL,
        ]);

        $this->fakeApi([
            ['date' => '2026-08-17', 'name' => 'Hari Kemerdekaan RI', 'is_national_holiday' => true],
        ]);

        $hasil = app(NationalHolidayImporter::class)->import(2026);

        $this->assertSame(1, $hasil['dihapus']);
        $this->assertDatabaseMissing('sla_holidays', ['tanggal' => '2026-05-15']);
        $this->assertDatabaseHas('sla_holidays', ['tanggal' => '2026-04-15']);
    }

    public function test_tahun_lain_tidak_ikut_terhapus(): void
    {
        SlaHoliday::create([
            'tanggal' => '2025-08-17',
            'keterangan' => 'Hari Kemerdekaan RI',
            'sumber' => SlaHoliday::SUMBER_IMPORT,
        ]);

        $this->fakeApi([
            ['date' => '2026-08-17', 'name' => 'Hari Kemerdekaan RI', 'is_national_holiday' => true],
        ]);

        app(NationalHolidayImporter::class)->import(2026);

        $this->assertDatabaseHas('sla_holidays', ['tanggal' => '2025-08-17']);
    }

    public function test_api_gagal_melempar_exception(): void
    {
        Http::fake(['*' => Http::response('Payment required', 402)]);

        $this->expectException(RuntimeException::class);

        app(NationalHolidayImporter::class)->import(2026);
    }

    public function test_admin_bisa_impor_lewat_halaman_pengaturan(): void
    {
        $this->fakeApi([
            ['date' => '2026-08-17', 'name' => 'Hari Kemerdekaan RI', 'is_national_holiday' => true],
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sla.pengaturan.libur.impor'), ['tahun' => 2026]);

        $response->assertRedirect(route('admin.sla.pengaturan'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('sla_holidays', [
            'tanggal' => '2026-08-17',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_impor_gagal_menampilkan_pesan_bukan_error_500(): void
    {
        Http::fake(['*' => Http::response('Payment required', 402)]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sla.pengaturan.libur.impor'), ['tahun' => 2026]);

        $response->assertRedirect(route('admin.sla.pengaturan'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('sla_holidays', 0);
    }
}
