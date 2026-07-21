<?php

namespace Tests\Unit\Sla;

use App\Services\Sla\WorkingTimeCalculator;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class WorkingTimeCalculatorTest extends TestCase
{
    /**
     * Senin-Jumat 08:00-16:00 (8 jam kerja/hari), tanpa libur tambahan.
     */
    private function calculator(array $holidays = []): WorkingTimeCalculator
    {
        return new WorkingTimeCalculator([1, 2, 3, 4, 5], '08:00', '16:00', $holidays);
    }

    public function test_durasi_dalam_satu_hari_kerja(): void
    {
        $calc = $this->calculator();

        // Senin, 2026-07-20 09:00 -> 11:30 (2.5 jam)
        $start = Carbon::create(2026, 7, 20, 9, 0);
        $end = Carbon::create(2026, 7, 20, 11, 30);

        $this->assertSame(150, $calc->elapsedWorkingMinutes($start, $end));
    }

    public function test_durasi_di_luar_jam_kerja_tidak_dihitung(): void
    {
        $calc = $this->calculator();

        // Senin 18:00 -> Selasa 07:00, keduanya di luar jam kerja 08:00-16:00
        $start = Carbon::create(2026, 7, 20, 18, 0);
        $end = Carbon::create(2026, 7, 21, 7, 0);

        $this->assertSame(0, $calc->elapsedWorkingMinutes($start, $end));
    }

    public function test_durasi_lintas_akhir_pekan_mengecualikan_sabtu_minggu(): void
    {
        $calc = $this->calculator();

        // Jumat 2026-07-24 15:00 -> Senin 2026-07-27 09:00
        // Jumat: 15:00-16:00 = 60 menit. Sabtu/Minggu (25-26 Jul) dilewati.
        // Senin: 08:00-09:00 = 60 menit.
        $start = Carbon::create(2026, 7, 24, 15, 0);
        $end = Carbon::create(2026, 7, 27, 9, 0);

        $this->assertSame(120, $calc->elapsedWorkingMinutes($start, $end));
    }

    public function test_durasi_mengecualikan_tanggal_libur(): void
    {
        // 2026-07-22 (Rabu) ditandai sebagai libur nasional.
        $calc = $this->calculator(['2026-07-22']);

        // Selasa 2026-07-21 08:00 -> Kamis 2026-07-23 16:00
        // Selasa penuh (8 jam=480), Rabu libur (0), Kamis penuh (480) = 960 menit.
        $start = Carbon::create(2026, 7, 21, 8, 0);
        $end = Carbon::create(2026, 7, 23, 16, 0);

        $this->assertSame(960, $calc->elapsedWorkingMinutes($start, $end));
    }

    public function test_permohonan_lintas_beberapa_hari_kerja_penuh(): void
    {
        $calc = $this->calculator();

        // Senin 08:00 -> Rabu 16:00 = 3 hari kerja penuh x 8 jam = 1440 menit.
        $start = Carbon::create(2026, 7, 20, 8, 0);
        $end = Carbon::create(2026, 7, 22, 16, 0);

        $this->assertSame(1440, $calc->elapsedWorkingMinutes($start, $end));
        $this->assertSame(24.0, $calc->elapsedWorkingHours($start, $end));
        $this->assertSame(3.0, $calc->elapsedWorkingDays($start, $end));
    }

    public function test_end_sebelum_start_mengembalikan_nol(): void
    {
        $calc = $this->calculator();

        $start = Carbon::create(2026, 7, 20, 12, 0);
        $end = Carbon::create(2026, 7, 20, 9, 0);

        $this->assertSame(0, $calc->elapsedWorkingMinutes($start, $end));
    }

    public function test_working_day_length_minutes(): void
    {
        $calc = $this->calculator();

        $this->assertSame(480, $calc->workingDayLengthMinutes());
    }

    /**
     * Regresi: Carbon 3 mengembalikan float dari diffInMinutes(). Sebelumnya akumulasi
     * dilakukan ke variabel int sehingga pecahan detik terpotong per hari (dan memicu
     * deprecation "Implicit conversion from float ... loses precision").
     */
    public function test_durasi_dengan_pecahan_detik_dibulatkan_bukan_dipotong(): void
    {
        $calc = $this->calculator();

        // Senin 09:00:00 -> 09:30:40 = 30 menit 40 detik, dibulatkan jadi 31.
        $start = Carbon::create(2026, 7, 20, 9, 0, 0);
        $end = Carbon::create(2026, 7, 20, 9, 30, 40);

        $this->assertSame(31, $calc->elapsedWorkingMinutes($start, $end));
    }

    /**
     * Regresi: pecahan detik pada beberapa hari kerja hanya boleh dibulatkan sekali
     * di akhir, bukan terpotong pada setiap iterasi harian.
     */
    public function test_pecahan_detik_lintas_hari_tidak_terpotong_berulang(): void
    {
        $calc = $this->calculator();

        // Senin 08:00:50 -> Rabu 16:00:00.
        // Senin 479 menit 10 detik + Selasa 480 + Rabu 480 = 1439.1667 -> 1439.
        $start = Carbon::create(2026, 7, 20, 8, 0, 50);
        $end = Carbon::create(2026, 7, 22, 16, 0, 0);

        $this->assertSame(1439, $calc->elapsedWorkingMinutes($start, $end));
    }
}
