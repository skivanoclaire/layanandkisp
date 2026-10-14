<?php

namespace Tests\Feature\MasterArsitektur;

use App\Models\MasterArsitektur\Item;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use App\Services\MasterArsitektur\Importer;
use App\Services\MasterArsitektur\Skema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * Modul Master Data Arsitektur: khusus role Admin, dasbor 7 kategori,
 * CRUD, dan impor/ekspor Excel/CSV.
 */
class MasterArsitekturTest extends TestCase
{
    use RefreshDatabase;

    private UnitKerja $dkisp;
    private UnitKerja $bapperida;
    private array $berkasSementara = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->dkisp = UnitKerja::factory()->create(['nama' => 'DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN']);
        $this->bapperida = UnitKerja::factory()->create(['nama' => 'BADAN PERENCANAAN PEMBANGUNAN, RISET DAN INOVASI DAERAH']);
    }

    protected function tearDown(): void
    {
        foreach ($this->berkasSementara as $berkas) {
            @unlink($berkas);
        }

        parent::tearDown();
    }

    private function pengguna(string $role): User
    {
        $user = User::factory()->create(['is_verified' => true]);
        DB::table('role_user')->insert(['role_id' => Role::where('name', $role)->value('id'), 'user_id' => $user->id]);

        return $user;
    }

    /** Workbook dengan susunan seperti sumbernya: baris 1 judul sheet, baris 2 judul kolom. */
    private function workbook(array $sheets): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($sheets as $nama => $rows) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($nama);
            $sheet->fromArray($rows, null, 'A1', true);
        }

        $path = tempnam(sys_get_temp_dir(), 'arsitektur') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $this->berkasSementara[] = $path;
    }

    private function sheetProsesBisnis(array $baris): array
    {
        return array_merge([
            ['Domain Proses Bisnis'],
            ['Visi', 'ID Proses Bisnis', 'Status', 'Nama Bisnis/Urusan', 'Sasaran Strategis (Isikan sasaran strategis eselon 1)',
                '→ RAB Level 1 Nasional (Dependency)', '→ RAB Level 2 (Dependency)', 'RAB Level 3 (Dependency)', 'Unit Kerja (Dependency)', 'Kolom Tak Dikenal'],
        ], $baris);
    }

    private function barisProsesBisnis(string $kode, string $nama, string $opd, string $rab1 = 'RAB.09 PEMERINTAHAN UMUM'): array
    {
        return ['Visi Kaltara', $kode, 'As-is', $nama, 'SS3', $rab1, 'RAB.09.03 INFORMASI', 'RAB.09.03.01 KOMUNIKASI', $opd, 'abaikan'];
    }

    public function test_hanya_role_admin_yang_dapat_mengakses(): void
    {
        $this->get(route('admin.master-arsitektur.index'))->assertRedirect();

        $user = $this->pengguna('User');
        $this->actingAs($user)->get(route('admin.master-arsitektur.kategori', 'proses-bisnis'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.master-arsitektur.impor.form'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.master-arsitektur.store', 'proses-bisnis'), ['jenis' => 'proses-bisnis', 'nama' => 'X'])->assertForbidden();

        $this->assertSame(0, Item::count());
    }

    public function test_setiap_kategori_dasbor_terbuka(): void
    {
        $admin = $this->pengguna('Admin');

        Item::create(['jenis' => 'server', 'nama' => 'xenkaltara', 'unit_kerja_id' => $this->dkisp->id, 'ref_1' => 'RAI.03 Platform']);
        Item::create(['jenis' => 'audit', 'nama' => 'Audit Indeks KAMI', 'ref_1' => 'RAK 02. Penerapan Keamanan']);

        $this->actingAs($admin)->get(route('admin.master-arsitektur.index'))
            ->assertRedirect(route('admin.master-arsitektur.kategori', 'proses-bisnis'));

        foreach (Skema::kategori() as $kunci => $kategori) {
            $this->actingAs($admin)->get(route('admin.master-arsitektur.kategori', $kunci))
                ->assertOk()
                ->assertSee($kategori['panel']['katalog']);

            // Kategori "data" dulu bertabrakan dengan route /data/{item} sehingga ekspor & tambah berujung 404
            $this->get(route('admin.master-arsitektur.ekspor', $kunci))->assertOk();
            $this->get(route('admin.master-arsitektur.create', $kunci))->assertOk();
        }

        $this->actingAs($admin)->get(route('admin.master-arsitektur.kategori', 'infrastruktur'))->assertSee('xenkaltara');
        $this->actingAs($admin)->get('/admin/master-arsitektur/tidak-ada')->assertNotFound();
    }

    public function test_filter_utama_proses_bisnis_tersedia(): void
    {
        $this->actingAs($this->pengguna('Admin'))
            ->get(route('admin.master-arsitektur.kategori', 'proses-bisnis'))
            ->assertOk()
            ->assertSeeInOrder(['OPD', 'RAB 1 - Sektor', 'RAB 2 - Urusan', 'RAB 3 - Fungsi'])
            ->assertSeeInOrder(['Daftar OPD', 'Sektor Pemerintahan', 'Urusan Pemerintahan', 'Katalog Proses Bisnis']);
    }

    public function test_tab_layanan_menampilkan_jumlah_dan_persentase_terdigitalisasi(): void
    {
        $admin = $this->pengguna('Admin');

        foreach (['Elektronik', 'Elektronik', 'Elektronik', 'Non Elektronik'] as $n => $metode) {
            Item::create([
                'jenis' => 'layanan', 'nama' => "Layanan $n", 'atribut' => ['metode_layanan' => $metode],
                'unit_kerja_id' => $n === 0 ? $this->dkisp->id : $this->bapperida->id,
            ]);
        }
        Item::create(['jenis' => 'layanan', 'nama' => 'Layanan tanpa metode']);

        $this->actingAs($admin)->get(route('admin.master-arsitektur.kategori', 'layanan'))
            ->assertOk()
            ->assertSeeInOrder(['Layanan Terdigitalisasi', '>3<', '60,0%', 'dari 5 layanan'], false);

        // Mengikuti filter yang aktif
        $this->actingAs($admin)->get(route('admin.master-arsitektur.kategori', ['kategori' => 'layanan', 'opd' => $this->bapperida->id]))
            ->assertOk()
            ->assertSeeInOrder(['Layanan Terdigitalisasi', '>2<', '66,7%', 'dari 3 layanan'], false);

        $this->actingAs($admin)->get(route('admin.master-arsitektur.kategori', 'proses-bisnis'))
            ->assertOk()
            ->assertDontSee('Terdigitalisasi');
    }

    public function test_impor_workbook_memetakan_opd_ke_master_data_instansi(): void
    {
        $path = $this->workbook([
            'Proses_Bisnis' => $this->sheetProsesBisnis([
                $this->barisProsesBisnis('KLTR-DAB 09.03.01.01', 'Pengelolaan Informasi Publik', 'Dinas Komunikasi, Informatika, Statistik dan Persandian'),
                $this->barisProsesBisnis('KLTR-DAB 09.03.01.02', 'Statistik Sektoral', '"Dinas Komunikasi, Informatika, Statistik dan Persandian"'),
                $this->barisProsesBisnis('KLTR-DAB 09.01.01.01', 'Perencanaan Pembangunan', 'Badan Perencanaan Pembangunan Daerah dan Litbang', 'RAB.03 PEMBANGUNAN KEWILAYAHAN'),
                $this->barisProsesBisnis('KLTR-DAB 09.01.01.02', 'Koordinasi SPBE', 'Tim Koordinasi SPBE'),
                ['Visi Kaltara', 'KLTR-DAB 00', 'As-is', null], // tanpa nama: dilewati
            ]),
            'Sheet Lain' => [['a', 'b'], [1, 2]],
        ]);

        $hasil = app(Importer::class)->impor($path, 'xlsx');

        $this->assertSame(['proses-bisnis'], array_keys($hasil));
        $this->assertSame(4, $hasil['proses-bisnis']['dibaca']);
        $this->assertSame(4, $hasil['proses-bisnis']['ditambah']);
        $this->assertSame(['Tim Koordinasi SPBE' => 1], $hasil['proses-bisnis']['tanpa_instansi']);

        $item = Item::where('kode', 'KLTR-DAB 09.03.01.01')->firstOrFail();
        $this->assertSame('Pengelolaan Informasi Publik', $item->nama);
        $this->assertSame($this->dkisp->id, $item->unit_kerja_id);
        $this->assertSame('RAB.09 PEMERINTAHAN UMUM', $item->ref_1);
        $this->assertSame('RAB.09.03 INFORMASI', $item->ref_2);
        $this->assertSame('RAB.09.03.01 KOMUNIKASI', $item->ref_3);
        $this->assertSame('SS3', $item->atribut['sasaran_strategis']);
        $this->assertArrayNotHasKey('kolom_tak_dikenal', $item->atribut);

        // Nama berkutip dan nama lama (alias) tetap terpetakan
        $this->assertSame($this->dkisp->id, Item::where('kode', 'KLTR-DAB 09.03.01.02')->value('unit_kerja_id'));
        $this->assertSame($this->bapperida->id, Item::where('kode', 'KLTR-DAB 09.01.01.01')->value('unit_kerja_id'));

        $belum = Item::where('kode', 'KLTR-DAB 09.01.01.02')->firstOrFail();
        $this->assertNull($belum->unit_kerja_id);
        $this->assertSame('Tim Koordinasi SPBE', $belum->unit_kerja_teks);

        // Filter dasbor
        $admin = $this->pengguna('Admin');
        $this->actingAs($admin)
            ->get(route('admin.master-arsitektur.kategori', ['kategori' => 'proses-bisnis', 'opd' => $this->dkisp->id]))
            ->assertOk()
            ->assertSee('Pengelolaan Informasi Publik')
            ->assertSee('Statistik Sektoral')
            ->assertDontSee('Perencanaan Pembangunan</a>', false);

        $this->actingAs($admin)
            ->get(route('admin.master-arsitektur.kategori', ['kategori' => 'proses-bisnis', 'ref_1' => 'RAB.03 PEMBANGUNAN KEWILAYAHAN']))
            ->assertOk()
            ->assertSee('Perencanaan Pembangunan</a>', false)
            ->assertDontSee('Statistik Sektoral</a>', false);

        $this->actingAs($admin)
            ->get(route('admin.master-arsitektur.kategori', ['kategori' => 'proses-bisnis', 'opd' => 'x']))
            ->assertOk()
            ->assertSee('Koordinasi SPBE</a>', false)
            ->assertDontSee('Statistik Sektoral</a>', false);
    }

    public function test_impor_ulang_memperbarui_dan_mode_ganti_menghapus_data_lama(): void
    {
        $importer = app(Importer::class);
        $opd = 'Dinas Komunikasi, Informatika, Statistik dan Persandian';

        $importer->impor($this->workbook(['Proses_Bisnis' => $this->sheetProsesBisnis([
            $this->barisProsesBisnis('A.01', 'Proses Satu', $opd),
            $this->barisProsesBisnis('A.02', 'Proses Dua', $opd),
        ])]), 'xlsx');
        Item::create(['jenis' => 'layanan', 'nama' => 'Layanan lain']);

        // Pemetaan manual untuk nama yang tidak dikenali harus bertahan saat impor ulang
        $importer->impor($this->workbook(['Proses_Bisnis' => $this->sheetProsesBisnis([
            $this->barisProsesBisnis('A.03', 'Proses Tiga', 'Tim Koordinasi SPBE'),
        ])]), 'xlsx');
        Item::where('kode', 'A.03')->firstOrFail()->update(['unit_kerja_id' => $this->bapperida->id]);

        $hasil = $importer->impor($this->workbook(['Proses_Bisnis' => $this->sheetProsesBisnis([
            $this->barisProsesBisnis('A.01', 'Proses Satu (revisi)', $opd),
            $this->barisProsesBisnis('A.03', 'Proses Tiga', 'Tim Koordinasi SPBE'),
            $this->barisProsesBisnis('A.04', 'Proses Empat', $opd),
        ])]), 'xlsx')['proses-bisnis'];

        $this->assertSame(1, $hasil['ditambah']);
        $this->assertSame(1, $hasil['diperbarui']);
        $this->assertSame([], $hasil['tanpa_instansi']);
        $this->assertSame(4, Item::where('jenis', 'proses-bisnis')->count());
        $this->assertSame('Proses Satu (revisi)', Item::where('kode', 'A.01')->value('nama'));
        $this->assertSame($this->bapperida->id, Item::where('kode', 'A.03')->value('unit_kerja_id'));

        $hasil = $importer->impor($this->workbook(['Proses_Bisnis' => $this->sheetProsesBisnis([
            $this->barisProsesBisnis('B.01', 'Proses Baru', $opd),
        ])]), 'xlsx', null, Importer::MODE_GANTI)['proses-bisnis'];

        $this->assertSame(4, $hasil['dihapus']);
        $this->assertSame(['B.01'], Item::where('jenis', 'proses-bisnis')->pluck('kode')->all());
        $this->assertSame(1, Item::where('jenis', 'layanan')->count(), 'Jenis lain tidak boleh ikut terhapus');
    }

    public function test_impor_csv_lewat_halaman_admin(): void
    {
        $admin = $this->pengguna('Admin');
        $csv = "ID Aplikasi;Nama Aplikasi;Status;Unit Operasional Teknologi;Tahun Pengembangan;Bahasa Pemrograman;RAA Level 1\n"
            . "KLTR-DAA 01.01.01;Portal Data;As-is;Dinas Komunikasi, Informatika, Statistik dan Persandian;2023;PHP;RAA.01 APLIKASI UMUM\n";

        // CSV tanpa jenis data ditolak
        $this->actingAs($admin)->post(route('admin.master-arsitektur.impor'), [
            'berkas' => UploadedFile::fake()->createWithContent('aplikasi.csv', $csv),
            'mode' => Importer::MODE_PERBARUI,
        ])->assertSessionHas('error');
        $this->assertSame(0, Item::count());

        $this->actingAs($admin)->post(route('admin.master-arsitektur.impor'), [
            'berkas' => UploadedFile::fake()->createWithContent('aplikasi.csv', $csv),
            'jenis' => 'aplikasi',
            'mode' => Importer::MODE_PERBARUI,
        ])->assertRedirect(route('admin.master-arsitektur.impor.form'))->assertSessionHas('hasil_impor');

        $item = Item::firstOrFail();
        $this->assertSame('aplikasi', $item->jenis);
        $this->assertSame('Portal Data', $item->nama);
        $this->assertSame('2023', $item->tahun);
        $this->assertSame($this->dkisp->id, $item->unit_kerja_id);
        $this->assertSame('PHP', $item->atribut['bahasa_pemrograman']);

        $this->actingAs($admin)->get(route('admin.master-arsitektur.impor.form'))->assertOk();

        $this->actingAs($admin)->post(route('admin.master-arsitektur.impor'), [
            'berkas' => UploadedFile::fake()->create('data.pdf', 10),
            'mode' => Importer::MODE_PERBARUI,
        ])->assertSessionHasErrors('berkas');
    }

    public function test_tambah_ubah_hapus_data(): void
    {
        $admin = $this->pengguna('Admin');

        $this->actingAs($admin)->get(route('admin.master-arsitektur.create', 'layanan'))->assertOk()->assertSee('Nama Layanan');
        $this->actingAs($admin)->get(route('admin.master-arsitektur.create', ['kategori' => 'infrastruktur', 'jenis' => 'server']))
            ->assertOk()->assertSee('Kapasitas Memori (RAM)');

        $this->actingAs($admin)->post(route('admin.master-arsitektur.store', 'layanan'), ['jenis' => 'layanan'])
            ->assertSessionHasErrors('nama');

        $this->actingAs($admin)->post(route('admin.master-arsitektur.store', 'layanan'), [
            'jenis' => 'layanan',
            'kode' => 'KLTR-DAL 02.04.03.01',
            'nama' => 'Layanan Permohonan Ijin Pelantikan',
            'status' => 'As-is',
            'unit_kerja_id' => $this->dkisp->id,
            'ref_1' => 'RAL.02 LAYANAN ADMINISTRASI PEMERINTAHAN',
            'atribut' => ['metode_layanan' => 'Elektronik', 'url' => null, 'bukan_kolom' => 'x'],
        ])->assertSessionHasNoErrors();

        $item = Item::firstOrFail();
        $this->assertSame('layanan', $item->jenis);
        $this->assertSame($this->dkisp->id, $item->unit_kerja_id);
        $this->assertSame($this->dkisp->nama, $item->unit_kerja_teks);
        $this->assertSame(['metode_layanan' => 'Elektronik'], $item->atribut);

        $this->actingAs($admin)->get(route('admin.master-arsitektur.show', $item))->assertOk()->assertSee('Elektronik');
        $this->actingAs($admin)->get(route('admin.master-arsitektur.edit', $item))->assertOk()->assertSee('KLTR-DAL 02.04.03.01');

        $this->actingAs($admin)->put(route('admin.master-arsitektur.update', $item), [
            'nama' => 'Layanan Izin Pelantikan',
            'status' => 'Metadata Upgrade (to-be)',
            'unit_kerja_id' => $this->bapperida->id,
            'atribut' => ['metode_layanan' => 'Non Elektronik'],
        ])->assertRedirect(route('admin.master-arsitektur.show', $item));

        $item->refresh();
        $this->assertSame('Layanan Izin Pelantikan', $item->nama);
        $this->assertNull($item->kode);
        $this->assertSame($this->bapperida->id, $item->unit_kerja_id);
        $this->assertSame('Non Elektronik', $item->atribut['metode_layanan']);

        // Jenis harus sesuai kategori pada URL
        $this->actingAs($admin)->post(route('admin.master-arsitektur.store', 'layanan'), ['jenis' => 'server', 'nama' => 'X'])
            ->assertStatus(422);

        $this->actingAs($admin)->delete(route('admin.master-arsitektur.destroy', $item))
            ->assertRedirect(route('admin.master-arsitektur.kategori', 'layanan'));
        $this->assertSame(0, Item::count());
    }

    public function test_hasil_ekspor_dapat_diimpor_kembali(): void
    {
        $admin = $this->pengguna('Admin');

        Item::create([
            'jenis' => 'peta-rencana', 'kode' => 'KLTR-DAB 05.01.01.01', 'nama' => 'Pengembangan Aplikasi JDIH',
            'unit_kerja_id' => $this->bapperida->id, 'unit_kerja_teks' => 'Badan Perencanaan Pembangunan Daerah dan Litbang',
            'tahun' => '2025', 'status' => 'Dalam Proses', 'ref_1' => 'RAB.05 KETERTIBAN UMUM DAN KESELAMATAN',
            'atribut' => ['program_permendagri' => 'PROGRAM PENGELOLAAN APLIKASI INFORMATIKA', 'program' => 'Pengembangan Aplikasi', 'muatan' => 'Aplikasi'],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.master-arsitektur.ekspor', 'peta-rencana'))->assertOk();
        $path = $this->berkasSementara[] = tempnam(sys_get_temp_dir(), 'ekspor') . '.xlsx';
        copy($response->baseResponse->getFile()->getPathname(), $path);

        $hasil = app(Importer::class)->impor($path, 'xlsx')['peta-rencana'];

        $this->assertNull($hasil['galat']);
        $this->assertSame(1, $hasil['dibaca']);
        $this->assertSame(0, $hasil['ditambah'], 'Baris hasil ekspor harus dikenali sebagai baris yang sama');
        $this->assertSame(1, Item::count());

        $item = Item::firstOrFail();
        $this->assertSame('PROGRAM PENGELOLAAN APLIKASI INFORMATIKA', $item->atribut['program_permendagri']);
        $this->assertSame('Pengembangan Aplikasi', $item->atribut['program']);
        $this->assertSame('2025', $item->tahun);
    }
}
