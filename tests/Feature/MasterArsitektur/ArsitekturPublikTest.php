<?php

namespace Tests\Feature\MasterArsitektur;

use App\Models\MasterArsitektur\Item;
use App\Models\UnitKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman publik /arsitektur: Master Data Arsitektur hanya-lihat, tanpa login.
 */
class ArsitekturPublikTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_dapat_melihat_setiap_kategori_publik(): void
    {
        $dkisp = UnitKerja::factory()->create(['nama' => 'DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN']);
        Item::create(['jenis' => 'layanan', 'kode' => 'LYN-01', 'nama' => 'Layanan Email Dinas', 'unit_kerja_id' => $dkisp->id]);
        Item::create(['jenis' => 'data', 'kode' => 'DT-01', 'nama' => 'Data Pegawai']);

        $this->get(route('arsitektur.index'))->assertRedirect(route('arsitektur.kategori', 'proses-bisnis'));

        foreach (config('master_arsitektur.publik.kategori') as $kunci) {
            $this->get(route('arsitektur.kategori', $kunci))
                ->assertOk()
                ->assertSee(config("master_arsitektur.kategori.$kunci.panel.katalog"));
        }

        $this->get('/arsitektur/data')->assertOk()->assertSee('Data Pegawai');
        $this->get(route('arsitektur.kategori', ['kategori' => 'layanan', 'q' => 'Email']))
            ->assertOk()
            ->assertSee('Layanan Email Dinas')
            ->assertSee('DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN');
    }

    public function test_halaman_publik_tidak_memuat_aksi_ubah(): void
    {
        $item = Item::create(['jenis' => 'layanan', 'kode' => 'LYN-01', 'nama' => 'Layanan Email Dinas', 'unit_kerja_teks' => 'Dinas Antah Berantah']);

        foreach ([route('arsitektur.kategori', 'layanan'), route('arsitektur.show', $item)] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Layanan Email Dinas')
                ->assertDontSee('Edit')
                ->assertDontSee('Hapus')
                ->assertDontSee('Tambah')
                ->assertDontSee('Impor')
                ->assertDontSee('Ekspor')
                ->assertDontSee('belum terpetakan')
                ->assertDontSee('admin/master-arsitektur');
        }

        // Tidak ada route tulis di /arsitektur
        $this->post('/arsitektur/layanan', ['jenis' => 'layanan', 'nama' => 'X'])->assertStatus(405);
        $this->put('/arsitektur/entri/' . $item->id, ['nama' => 'X'])->assertStatus(405);
        $this->delete('/arsitektur/entri/' . $item->id)->assertStatus(405);
        $this->get('/arsitektur/layanan/ekspor')->assertNotFound();

        $this->assertSame('Layanan Email Dinas', $item->fresh()->nama);
        $this->assertSame(1, Item::count());
    }

    public function test_kategori_infrastruktur_dan_keamanan_tidak_dibuka(): void
    {
        $server = Item::create(['jenis' => 'server', 'nama' => 'xenkaltara']);
        $audit = Item::create(['jenis' => 'audit', 'nama' => 'Audit Indeks KAMI']);

        $this->get('/arsitektur/infrastruktur')->assertNotFound();
        $this->get('/arsitektur/keamanan')->assertNotFound();
        $this->get(route('arsitektur.show', $server))->assertNotFound();
        $this->get(route('arsitektur.show', $audit))->assertNotFound();

        $this->get(route('arsitektur.kategori', 'aplikasi'))
            ->assertOk()
            ->assertDontSee('Infrastruktur')
            ->assertDontSee('Keamanan');
    }

    public function test_kolom_teknis_aplikasi_disembunyikan_di_detail(): void
    {
        $item = Item::create([
            'jenis' => 'aplikasi',
            'kode' => 'APL-01',
            'nama' => 'E-Layanan DKISP',
            'atribut' => ['uraian' => 'Portal layanan digital', 'nama_server' => 'srv-rahasia-01', 'basis_data' => 'db_rahasia'],
        ]);

        $this->get(route('arsitektur.show', $item))
            ->assertOk()
            ->assertSee('Portal layanan digital')
            ->assertDontSee('srv-rahasia-01')
            ->assertDontSee('db_rahasia')
            ->assertDontSee('Nama Server')
            ->assertDontSee('Identifikasi Kerentanan');
    }

    public function test_filter_belum_terpetakan_diabaikan(): void
    {
        Item::create(['jenis' => 'layanan', 'nama' => 'Layanan A', 'unit_kerja_teks' => 'Dinas Antah Berantah']);
        Item::create(['jenis' => 'layanan', 'nama' => 'Layanan B']);

        $this->get(route('arsitektur.kategori', ['kategori' => 'layanan', 'opd' => 'x']))
            ->assertOk()
            ->assertSee('Layanan A')
            ->assertSee('Layanan B');
    }
}
