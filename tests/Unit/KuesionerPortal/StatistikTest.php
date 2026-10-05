<?php

namespace Tests\Unit\KuesionerPortal;

use App\Services\KuesionerPortal\Statistik;
use PHPUnit\Framework\TestCase;

class StatistikTest extends TestCase
{
    private array $matriks = [[3, 4, 3], [2, 2, 3], [4, 5, 5], [1, 2, 1], [3, 3, 4]];

    public function test_cronbach_alpha(): void
    {
        $this->assertEqualsWithDelta(0.942857, Statistik::cronbachAlpha($this->matriks), 1e-6);
        // Item sejajar sempurna
        $this->assertEqualsWithDelta(1.0, Statistik::cronbachAlpha([[1, 2, 3], [2, 3, 4], [3, 4, 5], [4, 5, 6]]), 1e-9);
    }

    public function test_korelasi_item_total_terkoreksi(): void
    {
        $r = Statistik::korelasiItemTotalTerkoreksi($this->matriks);

        $this->assertEqualsWithDelta(0.992192, $r[0], 1e-6);
        $this->assertEqualsWithDelta(0.829652, $r[1], 1e-6);
        $this->assertEqualsWithDelta(0.861293, $r[2], 1e-6);
    }

    public function test_r_tabel_sesuai_tabel_r_product_moment(): void
    {
        $this->assertEqualsWithDelta(0.632, Statistik::rTabel(10), 0.001);
        $this->assertEqualsWithDelta(0.361, Statistik::rTabel(30), 0.001);
        $this->assertEqualsWithDelta(0.1966, Statistik::rTabel(100), 0.0005);
        $this->assertNull(Statistik::rTabel(2));
    }

    public function test_wilcoxon_dengan_nilai_kembar_dan_selisih_nol(): void
    {
        // d = [2, 0, -1, 3, 2, 1] → nol dibuang, W+ = 13,5, W− = 1,5
        $h = Statistik::wilcoxon([5, 4, 3, 5, 4, 2], [3, 4, 4, 2, 2, 1]);

        $this->assertSame(5, $h['n']);
        $this->assertEqualsWithDelta(1.5, $h['w'], 1e-9);
        $this->assertEqualsWithDelta(1.632993, $h['z'], 1e-5);
        $this->assertEqualsWithDelta(0.1025, $h['p'], 0.0005);
    }

    public function test_wilcoxon_tanpa_selisih(): void
    {
        $h = Statistik::wilcoxon([3, 3], [3, 3]);

        $this->assertSame(0, $h['n']);
        $this->assertNull($h['p']);
    }

    public function test_spearman_dan_peringkat_kembar(): void
    {
        $this->assertSame([0 => 1.5, 1 => 1.5, 2 => 3.0], Statistik::peringkat([2, 2, 5]));
        $this->assertEqualsWithDelta(1.0, Statistik::spearman([1, 2, 3, 4], [10, 20, 30, 40]), 1e-9);
        $this->assertEqualsWithDelta(-1.0, Statistik::spearman([1, 2, 3, 4], [9, 7, 5, 1]), 1e-9);
    }

    public function test_normal_cdf(): void
    {
        $this->assertEqualsWithDelta(0.975, Statistik::normalCdf(1.959964), 1e-6);
        $this->assertEqualsWithDelta(0.5, Statistik::normalCdf(0.0), 1e-7);
        $this->assertEqualsWithDelta(0.025, Statistik::normalCdf(-1.959964), 1e-6);
    }

    public function test_kelas_rata_rata_dan_interpretasi_tk(): void
    {
        $this->assertSame('Sangat rendah', Statistik::kelasRataRata(1.80));
        $this->assertSame('Sedang', Statistik::kelasRataRata(3.40));
        $this->assertSame('Tinggi', Statistik::kelasRataRata(3.41));
        $this->assertSame('Sangat tinggi', Statistik::kelasRataRata(4.21));

        $this->assertSame('Belum memenuhi harapan', Statistik::interpretasiTk(79.9));
        $this->assertSame('Memenuhi, perlu perbaikan', Statistik::interpretasiTk(100.0));
        $this->assertSame('Memenuhi/melampaui harapan', Statistik::interpretasiTk(100.1));
    }
}
