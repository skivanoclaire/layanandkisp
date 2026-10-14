<?php

namespace Tests\Unit\TataKelolaKeamanan;

use App\Models\TataKelolaKeamanan\Aktivitas;
use App\Services\TataKelolaKeamanan\Aturan;
use App\Services\TataKelolaKeamanan\Kapabilitas;
use Tests\TestCase;

/**
 * Aturan Bab 3 proposal v2: Tabel 3.2 (target), Tabel 3.4 (rating), kesepakatan 2/3,
 * dan penentuan tingkat kapabilitas (berhenti pada tingkat pertama yang belum F).
 */
class AturanKapabilitasTest extends TestCase
{
    public function test_target_level_mengikuti_tabel_3_2(): void
    {
        $this->assertSame(4, Aturan::targetLevel(100));
        $this->assertSame(4, Aturan::targetLevel(75));
        $this->assertSame(3, Aturan::targetLevel(74));
        $this->assertSame(3, Aturan::targetLevel(50));
        $this->assertSame(2, Aturan::targetLevel(49));
        $this->assertSame(2, Aturan::targetLevel(25));
        $this->assertSame(1, Aturan::targetLevel(24));
        $this->assertSame(1, Aturan::targetLevel(-40));
    }

    public function test_rating_mengikuti_tabel_3_4(): void
    {
        $this->assertSame('N', Aturan::rating(0));
        $this->assertSame('N', Aturan::rating(15));
        $this->assertSame('P', Aturan::rating(15.1));
        $this->assertSame('P', Aturan::rating(50));
        $this->assertSame('L', Aturan::rating(50.1));
        $this->assertSame('L', Aturan::rating(85));
        $this->assertSame('F', Aturan::rating(85.1));
        $this->assertSame('F', Aturan::rating(100));
    }

    public function test_kesepakatan_minimal_dua_pertiga(): void
    {
        $this->assertTrue(Aturan::disepakati(4, 6));   // tepat 2/3
        $this->assertTrue(Aturan::disepakati(5, 7));
        $this->assertFalse(Aturan::disepakati(3, 5));  // 60%
        $this->assertTrue(Aturan::disepakati(4, 5));
        $this->assertFalse(Aturan::disepakati(0, 0));  // belum ada penilai
    }

    private function aktivitas(array $spec)
    {
        // $spec: [[level, terpenuhi], ...]
        return collect($spec)->map(fn ($s) => new Aktivitas(['level' => $s[0], 'terpenuhi' => $s[1], 'uraian' => 'x']));
    }

    public function test_level_berhenti_pada_tingkat_pertama_yang_belum_f(): void
    {
        $k = new Kapabilitas();

        // Level 2: 7/7 (F), level 3: 3/4 (L) -> level 2
        $hasil = $k->hitung($this->aktivitas(array_merge(array_fill(0, 7, [2, true]), [[3, true], [3, true], [3, true], [3, false]])));
        $this->assertSame(2, $hasil['level']);
        $this->assertSame('F', $hasil['per_level'][2]['rating']);
        $this->assertSame('L', $hasil['per_level'][3]['rating']);
        $this->assertTrue($hasil['lengkap']);

        // Level 2 belum F -> level 1
        $hasil = $k->hitung($this->aktivitas([[2, true], [2, false], [3, true]]));
        $this->assertSame(1, $hasil['level']);
        $this->assertSame('P', $hasil['per_level'][2]['rating']);

        // Level 2 dan 3 F, level 4 tidak punya aktivitas -> level 3
        $hasil = $k->hitung($this->aktivitas([[2, true], [3, true], [5, false]]));
        $this->assertSame(3, $hasil['level']);
    }

    public function test_aktivitas_belum_dinilai_dihitung_belum_terpenuhi_dan_ditandai(): void
    {
        $hasil = (new Kapabilitas())->hitung($this->aktivitas([[2, true], [2, null]]));

        $this->assertSame(1, $hasil['level']);
        $this->assertFalse($hasil['lengkap']);
        $this->assertSame(1, $hasil['per_level'][2]['dinilai']);
    }

    public function test_tanpa_aktivitas_tingkat_tidak_ditentukan(): void
    {
        $hasil = (new Kapabilitas())->hitung(collect());

        $this->assertNull($hasil['level']);
        $this->assertFalse($hasil['lengkap']);
    }
}
