<?php

namespace App\Services\Dtsen;

use App\Models\DtsenAccountRequest;
use App\Models\DtsenDataRequest;
use App\Services\Sla\WorkingTimeCalculator;
use Illuminate\Support\Carbon;

/**
 * Parameter & perhitungan SLA per tahapan DTSEN.
 *
 * Target default mengikuti Juknis: akun 1 hari, verifikasi administrasi 1 hari,
 * verifikasi substansi 2 hari, pemrosesan & QA 2 hari, BAST & pemberian akses
 * 1 hari — total sekitar 7 hari kerja.
 */
class DtsenSlaService
{
    /** @return array<string, array{label: string, target: int, start: string, end: string}> */
    public static function stages(): array
    {
        return [
            'akun' => [
                'label' => 'Pembuatan Akun',
                'target' => 1,
                'start' => 'submitted_at',
                'end' => 'verified_at',
            ],
            'verif_administrasi' => [
                'label' => 'Verifikasi Administrasi',
                'target' => 1,
                'start' => 'submitted_at',
                'end' => 'verif_admin_at',
            ],
            'verif_substansi' => [
                'label' => 'Verifikasi Substansi',
                'target' => 2,
                'start' => 'verif_admin_at',
                'end' => 'verif_substansi_at',
            ],
            'pemrosesan_qa' => [
                'label' => 'Pemrosesan Data & QA',
                'target' => 2,
                'start' => 'diterima_at',
                'end' => 'pemrosesan_at',
            ],
            'akses' => [
                'label' => 'BAST & Pemberian Akses',
                'target' => 1,
                'start' => 'pemrosesan_at',
                'end' => 'akses_at',
            ],
        ];
    }

    /** Total target end-to-end (hari kerja). */
    public static function totalTarget(): int
    {
        return array_sum(array_column(self::stages(), 'target'));
    }

    private function calculator(): WorkingTimeCalculator
    {
        return WorkingTimeCalculator::default();
    }

    /**
     * Durasi satu tahap dalam hari kerja. Bila tahap belum selesai, dihitung
     * sampai sekarang (berjalan) sehingga keterlambatan tetap terlihat.
     *
     * @return array{days: float|null, running: bool, target: int, breached: bool}|null
     */
    public function stageDuration(DtsenDataRequest|DtsenAccountRequest $model, string $stageKey): ?array
    {
        $stage = self::stages()[$stageKey] ?? null;
        if (! $stage) {
            return null;
        }

        $start = $model->{$stage['start']} ?? null;
        if (! $start instanceof Carbon) {
            return null;
        }

        $end = $model->{$stage['end']} ?? null;
        $running = ! $end instanceof Carbon;
        $days = $this->calculator()->elapsedWorkingDays($start, $running ? now() : $end);

        return [
            'days' => $days,
            'running' => $running,
            'target' => $stage['target'],
            'breached' => $days > $stage['target'],
        ];
    }

    /**
     * Rekap kepatuhan SLA seluruh tahapan pada sekumpulan permohonan.
     *
     * @param  iterable<DtsenDataRequest>  $requests
     * @return array<string, array{label: string, target: int, selesai: int, tepat: int, lewat: int, rata2: float}>
     */
    public function summary(iterable $requests): array
    {
        $summary = [];
        foreach (self::stages() as $key => $stage) {
            if ($key === 'akun') {
                continue; // tahap akun direkap terpisah (model berbeda)
            }
            $summary[$key] = [
                'label' => $stage['label'],
                'target' => $stage['target'],
                'selesai' => 0,
                'tepat' => 0,
                'lewat' => 0,
                'rata2' => 0.0,
            ];
        }

        $totals = array_fill_keys(array_keys($summary), 0.0);

        foreach ($requests as $request) {
            foreach ($summary as $key => $_) {
                $result = $this->stageDuration($request, $key);
                if (! $result || $result['running']) {
                    continue;
                }

                $summary[$key]['selesai']++;
                $totals[$key] += $result['days'];
                if ($result['breached']) {
                    $summary[$key]['lewat']++;
                } else {
                    $summary[$key]['tepat']++;
                }
            }
        }

        foreach ($summary as $key => $row) {
            $summary[$key]['rata2'] = $row['selesai'] > 0
                ? round($totals[$key] / $row['selesai'], 2)
                : 0.0;
        }

        return $summary;
    }
}
