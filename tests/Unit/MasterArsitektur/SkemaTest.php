<?php

namespace Tests\Unit\MasterArsitektur;

use App\Services\MasterArsitektur\Skema;
use Tests\TestCase;

class SkemaTest extends TestCase
{
    public function test_judul_kolom_excel_dicocokkan_tanpa_panah_dan_keterangan(): void
    {
        $peta = Skema::petaJudul('proses-bisnis');

        $this->assertSame('ref_1', Skema::cocokkanJudul($peta, '→ RAB Level 1 Nasional (Dependency)'));
        $this->assertSame('ref_3', Skema::cocokkanJudul($peta, 'RAB Level 3 (Dependency)'));
        $this->assertSame('unit_kerja', Skema::cocokkanJudul($peta, 'Unit Kerja (Dependency)'));
        $this->assertSame('iku', Skema::cocokkanJudul($peta, 'Indikator Kinerja Utama (IKU) -- (Tuliskan indikator yang menjadi ukuran'));
        $this->assertSame('sasaran_strategis', Skema::cocokkanJudul($peta, "Sasaran Strategis (Isikan sasaran\nstrategis eselon 1)"));
        $this->assertNull(Skema::cocokkanJudul($peta, 'No'));
        $this->assertNull(Skema::cocokkanJudul($peta, null));
    }

    public function test_judul_yang_mirip_tidak_tertukar(): void
    {
        $peta = Skema::petaJudul('peta-rencana');
        $this->assertSame('program_permendagri', Skema::cocokkanJudul($peta, 'PROGRAM (Permedagri)'));
        $this->assertSame('program', Skema::cocokkanJudul($peta, 'PROGRAM'));
        $this->assertSame('nama', Skema::cocokkanJudul($peta, 'KEGIATAN'));
        $this->assertSame('nama_proses_bisnis', Skema::cocokkanJudul($peta, 'Nama Bisnis/Urusan'));

        $peta = Skema::petaJudul('aplikasi');
        $this->assertSame('luaran', Skema::cocokkanJudul($peta, 'Luaran (Merupakan hasil-hasil yang diperoleh)'));
        $this->assertSame('luaran_data', Skema::cocokkanJudul($peta, '→ Luaran Data (Dependency) - Merupakan identifikasi'));
        $this->assertSame('proses_bisnis', Skema::cocokkanJudul($peta, '← Proses Bisnis (Dependency) - Isikan semua proses bisnis'));

        $peta = Skema::petaJudul('layanan');
        $this->assertSame('rab_level_2', Skema::cocokkanJudul($peta, 'Urusan Pemerintahan → RAB Level 2'));
        $this->assertSame('ref_2', Skema::cocokkanJudul($peta, '→ RAL Level 2 (Dependency)'));
    }

    public function test_setiap_jenis_punya_kolom_nama_dan_masuk_tepat_satu_kategori(): void
    {
        $terpakai = [];
        foreach (Skema::kategori() as $kategori) {
            foreach ($kategori['jenis'] as $jenis) {
                $terpakai[] = $jenis;
            }
            foreach (array_keys($kategori['filter'] + $kategori['katalog']) as $kolom) {
                $this->assertContains($kolom, [...Skema::INTI, 'jenis'], "Filter/katalog [$kolom] harus kolom inti");
            }
        }

        $this->assertEqualsCanonicalizing(array_keys(Skema::jenis()), $terpakai);
        $this->assertCount(7, Skema::kategori());

        foreach (Skema::jenis() as $kunci => $jenis) {
            $this->assertArrayHasKey('nama', $jenis['kolom'], "Jenis [$kunci] tanpa kolom nama");
            foreach (Skema::kolomKunci($kunci) as $kolom) {
                $this->assertArrayHasKey($kolom, $jenis['kolom'], "Kolom kunci [$kolom] tidak ada pada jenis [$kunci]");
            }
            // Setiap kolom harus bisa ditemukan kembali dari judul ekspornya
            $peta = Skema::petaJudul($kunci);
            foreach ($jenis['kolom'] as $kolom => $definisi) {
                $this->assertSame($kolom, Skema::cocokkanJudul($peta, Skema::judulExcel($definisi)), "Judul ekspor kolom [$kunci.$kolom] tidak unik");
            }
        }
    }
}
