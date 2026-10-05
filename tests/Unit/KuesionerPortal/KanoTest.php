<?php

namespace Tests\Unit\KuesionerPortal;

use App\Services\KuesionerPortal\Kano;
use PHPUnit\Framework\TestCase;

class KanoTest extends TestCase
{
    public function test_tabel_evaluasi_mengikuti_berger(): void
    {
        $this->assertSame('O', Kano::kategori(1, 5));
        $this->assertSame('A', Kano::kategori(1, 2));
        $this->assertSame('A', Kano::kategori(1, 4));
        $this->assertSame('M', Kano::kategori(2, 5));
        $this->assertSame('M', Kano::kategori(4, 5));
        $this->assertSame('I', Kano::kategori(3, 3));
        $this->assertSame('R', Kano::kategori(5, 1));
        $this->assertSame('R', Kano::kategori(2, 1));
        $this->assertSame('Q', Kano::kategori(1, 1));
        $this->assertSame('Q', Kano::kategori(5, 5));
        $this->assertNull(Kano::kategori(null, 3));
    }

    public function test_kategori_tipis_dilaporkan_campuran_oleh_uji_fong(): void
    {
        $h = Kano::ringkas($this->jawaban(['A' => 10, 'O' => 5, 'M' => 3, 'I' => 2]));

        $this->assertSame('A', $h['kategori']);
        $this->assertSame('O', $h['kategori_kedua']);
        $this->assertTrue($h['campuran']);
        $this->assertSame('A/O', $h['kategori_tampil']);
        $this->assertEqualsWithDelta(5.0521, $h['fong']['batas'], 0.0001);
        $this->assertEqualsWithDelta(0.75, $h['better'], 1e-9);
        $this->assertEqualsWithDelta(-0.40, $h['worse'], 1e-9);
    }

    public function test_kategori_tegas_lolos_uji_fong(): void
    {
        $h = Kano::ringkas($this->jawaban(['M' => 15, 'O' => 3, 'I' => 2]));

        $this->assertSame('M', $h['kategori']);
        $this->assertFalse($h['campuran']);
        $this->assertSame('M', $h['kategori_tampil']);
    }

    public function test_aturan_berger_memilih_dari_i_r_bila_aom_tidak_dominan(): void
    {
        // A+O+M = 6 < I+R = 8, walaupun I bukan mayoritas mutlak
        $h = Kano::ringkas($this->jawaban(['I' => 8, 'A' => 3, 'O' => 2, 'M' => 1]));

        $this->assertSame('I', $h['kategori']);
    }

    public function test_jawaban_questionable_dikeluarkan_dari_n(): void
    {
        $h = Kano::ringkas(['Q', 'Q', 'M', null]);

        $this->assertSame(1, $h['n']);
        $this->assertSame(2, $h['frekuensi']['Q']);
        $this->assertEqualsWithDelta(2 / 3, $h['proporsi_q'], 1e-9);
        $this->assertSame('M', $h['kategori']);
    }

    private function jawaban(array $frekuensi): array
    {
        $hasil = [];
        foreach ($frekuensi as $kategori => $jumlah) {
            array_push($hasil, ...array_fill(0, $jumlah, $kategori));
        }

        return $hasil;
    }
}
