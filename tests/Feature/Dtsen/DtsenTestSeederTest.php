<?php

namespace Tests\Feature\Dtsen;

use App\Models\DtsenAccountRequest;
use App\Models\DtsenComplaint;
use App\Models\DtsenDataRequest;
use App\Models\DtsenDestructionReport;
use App\Models\DtsenIncidentReport;
use App\Models\DtsenRequestLog;
use App\Models\DtsenUtilizationReport;
use App\Models\User;
use Database\Seeders\DtsenTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Menjaga agar DtsenTestSeeder tetap menghasilkan data uji yang lengkap dan wajar.
 *
 * Seeder ini adalah alat bantu uji coba manual, jadi yang dijaga bukan hanya
 * "berhasil jalan" melainkan juga: seluruh status terwakili, datanya konsisten
 * dengan aturan modul, dan halaman-halamannya benar-benar terbuka.
 */
class DtsenTestSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(DtsenTestSeeder::class);
    }

    public function test_seluruh_status_permohonan_terwakili(): void
    {
        $adaStatus = DtsenDataRequest::query()->distinct()->pluck('status')->all();

        foreach (array_keys(DtsenDataRequest::statusLabels()) as $status) {
            $this->assertContains(
                $status,
                $adaStatus,
                "Status permohonan '{$status}' tidak punya contoh data, padahal perlu diuji coba."
            );
        }
    }

    public function test_seluruh_status_akun_terwakili(): void
    {
        $adaStatus = DtsenAccountRequest::query()->distinct()->pluck('status')->all();

        foreach (array_keys(DtsenAccountRequest::statusLabels()) as $status) {
            $this->assertContains($status, $adaStatus, "Status akun '{$status}' belum ada contoh datanya.");
        }
    }

    public function test_data_yang_dihasilkan_konsisten_dengan_aturan_modul(): void
    {
        $perStatus = DtsenDataRequest::all()->keyBy('status');

        // Token aktif hanya untuk yang datanya memang tersedia.
        $this->assertNotNull($perStatus['data_tersedia']->activeToken());
        $this->assertNull($perStatus['kedaluwarsa']->activeToken());

        // Level 4 wajib BAST; pada status menunggu_bast berkasnya sudah diunggah
        // pemohon tetapi belum disahkan DKISP.
        $menungguBast = $perStatus['menunggu_bast'];
        $this->assertSame(4, $menungguBast->level_akses);
        $this->assertNotNull($menungguBast->bast_file_path);
        $this->assertFalse($menungguBast->bast_verified);
        $this->assertNotNull($menungguBast->pemrosesan_at);

        // Penolakan substansi selalu disertai alasan dan bersifat final.
        $this->assertNotEmpty($perStatus['ditolak']->sub_alasan_penolakan);
        $this->assertTrue($perStatus['ditolak']->isFinal());

        // Pengembalian administrasi selalu disertai catatan perbaikan.
        $this->assertNotEmpty($perStatus['perlu_perbaikan']->adm_catatan);
        $this->assertTrue($perStatus['perlu_perbaikan']->isEditableByOwner());

        // Klarifikasi punya berita acaranya.
        $this->assertTrue($perStatus['klarifikasi']->clarifications()->exists());

        // Draft belum diajukan.
        $this->assertNull($perStatus['draft']->submitted_at);

        // Setiap permohonan punya variabel; level 4 punya dokumen pendukung.
        $this->assertSame(0, DtsenDataRequest::doesntHave('requestVariables')->count());
        $this->assertSame(
            0,
            DtsenDataRequest::where('level_akses', 4)->where('status', '!=', 'draft')->doesntHave('documents')->count()
        );
    }

    public function test_menyediakan_contoh_tahap_5_dan_pengaduan(): void
    {
        $this->assertTrue(DtsenUtilizationReport::where('status', DtsenUtilizationReport::STATUS_TERKIRIM)->exists());
        $this->assertTrue(DtsenUtilizationReport::where('status', DtsenUtilizationReport::STATUS_PERLU_PERBAIKAN)->exists());

        // Satu berita acara sudah disampaikan, satu masih draft dan lewat tenggat
        // supaya peringatan keterlambatan bisa dilihat.
        $this->assertTrue(DtsenDestructionReport::where('status', DtsenDestructionReport::STATUS_DILAPORKAN)->exists());
        $this->assertTrue(DtsenDestructionReport::get()->contains(fn ($r) => $r->isOverdue()));

        // Insiden terlambat harus sudah ditandai dan dieskalasi.
        $terlambat = DtsenIncidentReport::where('terlambat', true)->first();
        $this->assertNotNull($terlambat);
        $this->assertNotNull($terlambat->escalated_at);

        // Pengaduan anonim menyembunyikan identitas pada rekap.
        $anonim = DtsenComplaint::where('is_anonim', true)->first();
        $this->assertNotNull($anonim);
        $this->assertSame('Anonim', $anonim->displayName());
    }

    public function test_akun_nonaktif_punya_permintaan_aktivasi_ulang_menunggu(): void
    {
        $nonaktif = DtsenAccountRequest::where('status', DtsenAccountRequest::STATUS_DISETUJUI)
            ->where('is_active', false)
            ->first();

        $this->assertNotNull($nonaktif, 'Perlu contoh akun nonaktif untuk menguji alur aktivasi ulang.');
        $this->assertTrue(
            $nonaktif->reactivations()->where('status', 'diajukan')->exists(),
            'Akun nonaktif seharusnya punya permintaan aktivasi ulang yang menunggu keputusan.'
        );
    }

    public function test_halaman_pemohon_terbuka_dengan_data_seeder(): void
    {
        $pemohon = User::where('email', 'dtsen.opd@kaltaraprov.go.id')->firstOrFail();

        foreach ([
            'user.dtsen.akun.index',
            'user.dtsen.permohonan.index',
            'user.dtsen.perpanjangan.index',
            'user.dtsen.pemanfaatan.index',
            'user.dtsen.pemusnahan.index',
            'user.dtsen.insiden.index',
            'user.dtsen.pengaduan.index',
        ] as $route) {
            $this->actingAs($pemohon)->get(route($route))->assertOk();
        }

        // Detail tiap permohonan harus terbuka pada semua status.
        foreach (DtsenDataRequest::pluck('id') as $id) {
            $this->actingAs($pemohon)->get(route('user.dtsen.permohonan.show', $id))->assertOk();
        }
    }

    public function test_halaman_internal_terbuka_dengan_data_seeder(): void
    {
        $dkisp = User::where('email', 'dtsen.dkisp@kaltaraprov.go.id')->firstOrFail();
        $bapperida = User::where('email', 'dtsen.bapperida@kaltaraprov.go.id')->firstOrFail();

        foreach ([
            'admin.dtsen.akun.index',
            'admin.dtsen.permohonan.index',
            'admin.dtsen.perpanjangan.index',
            'admin.dtsen.pengaduan.index',
            'admin.dtsen.laporan.pemanfaatan',
            'admin.dtsen.laporan.pemusnahan',
            'admin.dtsen.laporan.insiden',
            'admin.dtsen.dashboard',
            'admin.dtsen.variables.index',
        ] as $route) {
            $this->actingAs($dkisp)->get(route($route))->assertOk();
        }

        foreach (DtsenDataRequest::where('status', '!=', 'draft')->pluck('id') as $id) {
            $this->actingAs($dkisp)->get(route('admin.dtsen.permohonan.show', $id))->assertOk();
        }

        $this->actingAs($bapperida)->get(route('admin.dtsen.substansi.index'))->assertOk();

        foreach (DtsenDataRequest::whereIn('status', [
            DtsenDataRequest::STATUS_VERIF_SUBSTANSI,
            DtsenDataRequest::STATUS_KLARIFIKASI,
            DtsenDataRequest::STATUS_DITERIMA,
            DtsenDataRequest::STATUS_DITOLAK,
        ])->pluck('id') as $id) {
            $this->actingAs($bapperida)->get(route('admin.dtsen.substansi.show', $id))->assertOk();
        }
    }

    public function test_dijalankan_ulang_tidak_menggandakan_data(): void
    {
        $sebelum = [
            'akun' => DtsenAccountRequest::count(),
            'permohonan' => DtsenDataRequest::count(),
            'insiden' => DtsenIncidentReport::count(),
            'pengaduan' => DtsenComplaint::count(),
            'log' => DtsenRequestLog::count(),
            'user' => User::where('email', 'like', 'dtsen.%')->count(),
        ];

        $this->seed(DtsenTestSeeder::class);

        $this->assertSame($sebelum['akun'], DtsenAccountRequest::count());
        $this->assertSame($sebelum['permohonan'], DtsenDataRequest::count());
        $this->assertSame($sebelum['insiden'], DtsenIncidentReport::count());
        $this->assertSame($sebelum['pengaduan'], DtsenComplaint::count());
        $this->assertSame($sebelum['user'], User::where('email', 'like', 'dtsen.%')->count());
        // Jejak audit juga tidak boleh menumpuk tiap kali seeder dijalankan.
        $this->assertSame($sebelum['log'], DtsenRequestLog::count());
    }

    public function test_tidak_menyentuh_data_dtsen_milik_pengguna_lain(): void
    {
        $orangLain = User::factory()->create(['is_verified' => true]);

        $milikOrangLain = DtsenAccountRequest::create([
            'user_id' => $orangLain->id,
            'nomor_surat' => '999/LAIN/2026',
            'sifat_surat' => 'biasa',
            'tanggal_surat' => now()->toDateString(),
            'narahubung_nama' => 'Narahubung Lain',
            'narahubung_kontak' => '0800000000',
            'narahubung_email' => 'lain@kaltaraprov.go.id',
            'status' => DtsenAccountRequest::STATUS_DIAJUKAN,
            'submitted_at' => now(),
            'consent_true' => true,
        ]);

        $this->seed(DtsenTestSeeder::class);

        $this->assertDatabaseHas('dtsen_account_requests', [
            'id' => $milikOrangLain->id,
            'nomor_surat' => '999/LAIN/2026',
        ]);
    }
}
