<?php

namespace App\Services\Sla;

use App\Models\SlaSetting;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Menghitung capaian SLA on-the-fly dari tabel-tabel permohonan existing,
 * berdasarkan konfigurasi di SlaServiceRegistry + target di tabel sla_settings.
 *
 * Tidak menyimpan histori/snapshot — setiap pemanggilan query ulang tabel sumber,
 * dibatasi rentang tanggal (default bulan berjalan) supaya tetap ringan.
 */
class SlaCalculationService
{
    private WorkingTimeCalculator $calculator;

    public function __construct(?WorkingTimeCalculator $calculator = null)
    {
        $this->calculator = $calculator ?? WorkingTimeCalculator::default();
    }

    /**
     * Ringkasan capaian SLA seluruh layanan dalam rentang tanggal tertentu.
     *
     * @return array{services: array<int, array<string, mixed>>, totals: array<string, mixed>}
     */
    public function summary(?Carbon $from = null, ?Carbon $to = null): array
    {
        [$from, $to] = $this->resolveRange($from, $to);

        $services = [];
        foreach (SlaServiceRegistry::all() as $key => $meta) {
            $services[] = $this->summaryFor($key, $from, $to);
        }

        $totals = [
            'total' => array_sum(array_column($services, 'total')),
            'selesai' => array_sum(array_column($services, 'selesai')),
            'ditolak' => array_sum(array_column($services, 'ditolak')),
            'proses' => array_sum(array_column($services, 'proses')),
            'menunggu' => array_sum(array_column($services, 'menunggu')),
            'achieved' => array_sum(array_column($services, 'achieved')),
            'breached' => array_sum(array_column($services, 'breached')),
        ];
        $closed = $totals['achieved'] + $totals['breached'];
        $totals['achieved_pct'] = $closed > 0 ? round($totals['achieved'] / $closed * 100, 1) : null;

        return ['services' => $services, 'totals' => $totals, 'from' => $from, 'to' => $to];
    }

    /**
     * Ringkasan capaian SLA satu layanan.
     */
    public function summaryFor(string $serviceKey, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $meta = SlaServiceRegistry::find($serviceKey);
        if (! $meta) {
            throw new \InvalidArgumentException("Layanan SLA '{$serviceKey}' tidak dikenal.");
        }

        [$from, $to] = $this->resolveRange($from, $to);
        $target = SlaSetting::where('service_key', $serviceKey)->first();
        $targetMinutes = $this->targetMinutes($target);

        $rows = $this->baseQuery($meta, $from, $to)->get();

        $counts = ['menunggu' => 0, 'proses' => 0, 'selesai' => 0, 'ditolak' => 0];
        $durations = [];
        $achieved = 0;
        $breached = 0;

        foreach ($rows as $row) {
            $hydrated = $this->hydrateRow($row, $meta, $targetMinutes);

            $counts[$hydrated['bucket']]++;

            if ($hydrated['is_closed']) {
                $durations[] = $hydrated['duration_minutes'];
                if ($targetMinutes !== null) {
                    $hydrated['achieved'] ? $achieved++ : $breached++;
                }
            }
        }

        $avgMinutes = count($durations) > 0 ? array_sum($durations) / count($durations) : null;
        $closedCount = $achieved + $breached;

        return [
            'service_key' => $serviceKey,
            'label' => $meta['label'],
            'group' => $meta['group'],
            'total' => array_sum($counts),
            'menunggu' => $counts['menunggu'],
            'proses' => $counts['proses'],
            'selesai' => $counts['selesai'],
            'ditolak' => $counts['ditolak'],
            'target_value' => $target?->target_value,
            'target_unit' => $target?->target_unit,
            'target_active' => (bool) $target?->is_active,
            'avg_duration_hours' => $avgMinutes !== null ? round($avgMinutes / 60, 2) : null,
            'achieved' => $achieved,
            'breached' => $breached,
            'achieved_pct' => $closedCount > 0 ? round($achieved / $closedCount * 100, 1) : null,
        ];
    }

    /**
     * Daftar permohonan 1 layanan beserta durasi & status capaian SLA, dipaginate.
     */
    public function detail(string $serviceKey, ?Carbon $from = null, ?Carbon $to = null, int $perPage = 25, int $page = 1): LengthAwarePaginator
    {
        $meta = SlaServiceRegistry::find($serviceKey);
        if (! $meta) {
            throw new \InvalidArgumentException("Layanan SLA '{$serviceKey}' tidak dikenal.");
        }

        [$from, $to] = $this->resolveRange($from, $to);
        $target = SlaSetting::where('service_key', $serviceKey)->first();
        $targetMinutes = $this->targetMinutes($target);

        $paginator = $this->baseQuery($meta, $from, $to)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        $paginator->getCollection()->transform(function ($row) use ($meta, $targetMinutes) {
            return (object) array_merge((array) $row, $this->hydrateRow($row, $meta, $targetMinutes));
        });

        return $paginator;
    }

    /**
     * Status yang dikecualikan (mis. draft) disaring di level SQL supaya summary()
     * dan detail() konsisten — sebelumnya detail() ikut menampilkan draft sehingga
     * jumlahnya tidak cocok dengan dashboard.
     */
    private function baseQuery(array $meta, Carbon $from, Carbon $to)
    {
        $query = DB::table($meta['table'])
            ->whereBetween('created_at', [$from, $to]);

        if (! empty($meta['status_exclude'])) {
            $query->whereNotIn('status', $meta['status_exclude']);
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function hydrateRow(object $row, array $meta, ?int $targetMinutes): array
    {
        $bucket = $this->bucketFor($row->status ?? null, $meta);

        $start = $this->firstNonNullColumn($row, $meta['start_columns']) ?? $row->created_at;
        $startAt = $start ? Carbon::parse($start) : null;

        $endAt = null;
        $isClosed = false;

        if ($bucket === 'selesai') {
            $endAt = $this->firstNonNullColumn($row, $meta['end_success_columns']);
            $isClosed = true;
        } elseif ($bucket === 'ditolak') {
            $endAt = $this->firstNonNullColumn($row, $meta['end_rejected_columns']);
            $isClosed = true;
        }

        if ($isClosed && $endAt === null && isset($meta['fallback_end_column'])) {
            $endAt = $row->{$meta['fallback_end_column']} ?? null;
        }

        $endAt = $endAt ? Carbon::parse($endAt) : null;

        // Untuk permohonan yang masih berjalan, hitung durasi berjalan s.d. sekarang
        // (dipakai untuk menandai "berpotensi terlambat"), tapi tidak dihitung achieved/breached.
        $effectiveEnd = $endAt ?? Carbon::now();

        $durationMinutes = $startAt
            ? $this->calculator->elapsedWorkingMinutes($startAt, $effectiveEnd)
            : 0;

        $achieved = null;
        if ($isClosed && $targetMinutes !== null) {
            $achieved = $durationMinutes <= $targetMinutes;
        }

        $overdue = ! $isClosed && $targetMinutes !== null && $durationMinutes > $targetMinutes;

        return [
            'bucket' => $bucket,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'is_closed' => $isClosed,
            'duration_minutes' => $durationMinutes,
            'duration_hours' => round($durationMinutes / 60, 2),
            'achieved' => $achieved,
            'overdue' => $overdue,
        ];
    }

    private function bucketFor(?string $status, array $meta): string
    {
        if (in_array($status, $meta['status_selesai'] ?? [], true)) {
            return 'selesai';
        }
        if (in_array($status, $meta['status_ditolak'] ?? [], true)) {
            return 'ditolak';
        }
        if (in_array($status, $meta['status_proses'] ?? [], true)) {
            return 'proses';
        }

        return 'menunggu';
    }

    private function firstNonNullColumn(object $row, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (! empty($row->{$column} ?? null)) {
                return $row->{$column};
            }
        }

        return null;
    }

    /**
     * null bila layanan belum punya target atau targetnya dinonaktifkan —
     * capaian achieved/breached tidak dihitung untuk kasus tersebut.
     */
    private function targetMinutes(?SlaSetting $target): ?int
    {
        if (! $target || ! $target->is_active) {
            return null;
        }

        return $target->target_unit === 'jam'
            ? $target->target_value * 60
            : $target->target_value * $this->calculator->workingDayLengthMinutes();
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveRange(?Carbon $from, ?Carbon $to): array
    {
        $from = ($from ?? Carbon::now()->startOfMonth())->copy()->startOfDay();
        $to = ($to ?? Carbon::now()->endOfMonth())->copy()->endOfDay();

        return [$from, $to];
    }
}
