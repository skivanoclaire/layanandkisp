<?php

namespace App\Services\Sla;

use App\Models\SlaHoliday;
use App\Models\SlaWorkingHourSetting;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Menghitung durasi kerja (jam kerja) antara dua waktu, mengecualikan
 * hari non-kerja (default Sabtu/Minggu) dan tanggal libur nasional/cuti bersama.
 *
 * Dibuat stateless (config di-inject via constructor, bukan query DB langsung) supaya
 * mudah di-unit-test dengan tanggal & libur tetap. Gunakan WorkingTimeCalculator::default()
 * untuk instance yang membaca pengaturan aktif dari database.
 */
class WorkingTimeCalculator
{
    /** @var array<int> Angka hari ISO-8601: 1=Senin ... 7=Minggu */
    private array $workingDays;

    private string $jamMulai; // 'H:i:s'
    private string $jamSelesai; // 'H:i:s'

    /** @var array<string, bool> Tanggal libur berformat 'Y-m-d' sebagai key */
    private array $holidayDates;

    /**
     * @param  array<int>  $workingDays
     * @param  array<string>  $holidayDates  Daftar tanggal 'Y-m-d'
     */
    public function __construct(array $workingDays, string $jamMulai, string $jamSelesai, array $holidayDates = [])
    {
        $this->workingDays = $workingDays;
        $this->jamMulai = $jamMulai;
        $this->jamSelesai = $jamSelesai;
        $this->holidayDates = array_fill_keys($holidayDates, true);
    }

    /**
     * Instance yang membaca pengaturan jam kerja & daftar libur aktif dari database.
     */
    public static function default(): self
    {
        $setting = SlaWorkingHourSetting::current();
        $holidays = SlaHoliday::pluck('tanggal')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->all();

        return new self(
            $setting->hari_kerja ?: [1, 2, 3, 4, 5],
            $setting->jam_mulai,
            $setting->jam_selesai,
            $holidays,
        );
    }

    /**
     * Panjang satu hari kerja dalam menit (dipakai untuk konversi target "hari_kerja").
     */
    public function workingDayLengthMinutes(): int
    {
        $start = Carbon::parse($this->jamMulai);
        $end = Carbon::parse($this->jamSelesai);

        // Carbon 3 mengembalikan float; bulatkan eksplisit agar tidak terpotong diam-diam.
        return (int) round($start->diffInMinutes($end));
    }

    /**
     * Total menit kerja antara $start dan $end, hanya menghitung irisan dengan
     * jendela jam kerja pada hari-hari kerja yang bukan tanggal libur.
     */
    public function elapsedWorkingMinutes(CarbonInterface $start, CarbonInterface $end): int
    {
        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        $start = Carbon::instance($start->copy());
        $end = Carbon::instance($end->copy());

        // Carbon 3 mengembalikan float dari diffInMinutes(), jadi akumulasi dilakukan
        // sebagai float lalu dibulatkan sekali di akhir — bukan dipotong per hari.
        $minutes = 0.0;
        $cursor = $start->copy()->startOfDay();
        $lastDay = $end->copy()->startOfDay();

        while ($cursor->lessThanOrEqualTo($lastDay)) {
            if ($this->isWorkingDay($cursor)) {
                [$dayStart, $dayEnd] = $this->workingWindowFor($cursor);

                $overlapStart = $dayStart->greaterThan($start) ? $dayStart : $start;
                $overlapEnd = $dayEnd->lessThan($end) ? $dayEnd : $end;

                if ($overlapEnd->greaterThan($overlapStart)) {
                    $minutes += $overlapStart->diffInMinutes($overlapEnd);
                }
            }

            $cursor->addDay();
        }

        return (int) round($minutes);
    }

    public function elapsedWorkingHours(CarbonInterface $start, CarbonInterface $end): float
    {
        return round($this->elapsedWorkingMinutes($start, $end) / 60, 2);
    }

    /**
     * Durasi dalam satuan "hari_kerja" (menit kerja / panjang 1 hari kerja).
     */
    public function elapsedWorkingDays(CarbonInterface $start, CarbonInterface $end): float
    {
        $dayLength = $this->workingDayLengthMinutes();

        if ($dayLength <= 0) {
            return 0.0;
        }

        return round($this->elapsedWorkingMinutes($start, $end) / $dayLength, 2);
    }

    private function isWorkingDay(Carbon $day): bool
    {
        if (! in_array($day->isoWeekday(), $this->workingDays, true)) {
            return false;
        }

        return ! isset($this->holidayDates[$day->format('Y-m-d')]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function workingWindowFor(Carbon $day): array
    {
        $dayStart = $day->copy()->setTimeFromTimeString($this->jamMulai);
        $dayEnd = $day->copy()->setTimeFromTimeString($this->jamSelesai);

        return [$dayStart, $dayEnd];
    }
}
