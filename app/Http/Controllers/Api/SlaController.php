<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SlaSummaryResource;
use App\Models\SlaSetting;
use App\Services\Sla\SlaCalculationService;
use App\Services\Sla\SlaServiceRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Ekspos hasil capaian SLA layanan digital untuk dikonsumsi Portal/Aplikasi lain.
 * Hanya mengembalikan data agregat (bukan detail permohonan individual/PII).
 * Diamankan middleware api.whitelist + api.key, sama seperti endpoint master data.
 */
class SlaController extends Controller
{
    public function __construct(private SlaCalculationService $sla)
    {
    }

    /**
     * GET /api/v1/sla/summary?bulan=&tahun=
     * Ringkasan capaian SLA seluruh layanan pada periode tertentu (default bulan berjalan).
     */
    public function summary(Request $request)
    {
        // Tanpa ini, capaian SLA terbit null sampai ada admin yang membuka halaman
        // pengaturan SLA di web — konsumen API tidak boleh bergantung pada hal itu.
        SlaSetting::ensureDefaults();

        [$from, $to] = $this->resolvePeriod($request);

        $result = $this->sla->summary($from, $to);

        return SlaSummaryResource::collection($result['services'])
            ->additional([
                'success' => true,
                'periode' => ['dari' => $from->toDateString(), 'sampai' => $to->toDateString()],
                'ringkasan' => [
                    'total_permohonan' => $result['totals']['total'],
                    'jumlah_tercapai' => $result['totals']['achieved'],
                    'jumlah_terlambat' => $result['totals']['breached'],
                    'capaian_sla_persen' => $result['totals']['achieved_pct'],
                ],
            ]);
    }

    /**
     * GET /api/v1/sla/layanan/{serviceKey}?bulan=&tahun=
     * Ringkasan capaian SLA 1 layanan.
     */
    public function show(Request $request, string $serviceKey)
    {
        if (! SlaServiceRegistry::find($serviceKey)) {
            return response()->json([
                'success' => false,
                'message' => "Layanan '{$serviceKey}' tidak ditemukan.",
            ], 404);
        }

        SlaSetting::ensureDefaults();

        [$from, $to] = $this->resolvePeriod($request);

        $result = $this->sla->summaryFor($serviceKey, $from, $to);

        return (new SlaSummaryResource($result))
            ->additional([
                'success' => true,
                'periode' => ['dari' => $from->toDateString(), 'sampai' => $to->toDateString()],
            ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolvePeriod(Request $request): array
    {
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);

        $bulan = $bulan >= 1 && $bulan <= 12 ? $bulan : now()->month;
        $tahun = $tahun >= 2020 && $tahun <= 2100 ? $tahun : now()->year;

        $from = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        return [$from, $to];
    }
}
