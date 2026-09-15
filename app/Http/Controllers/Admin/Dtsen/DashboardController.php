<?php

namespace App\Http\Controllers\Admin\Dtsen;

use App\Http\Controllers\Controller;
use App\Models\DtsenAccessToken;
use App\Models\DtsenAccountRequest;
use App\Models\DtsenComplaint;
use App\Models\DtsenDataRequest;
use App\Models\DtsenDestructionReport;
use App\Models\DtsenIncidentReport;
use App\Models\DtsenUtilizationReport;
use App\Services\Dtsen\DtsenSlaService;
use Illuminate\Http\Request;

/**
 * Dashboard monitoring Tim Pelaksana: rekap permohonan, SLA, pemanfaatan, dan
 * insiden — bahan evaluasi tahunan (Bab VIII huruf C Juknis).
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request, DtsenSlaService $sla)
    {
        $tahun = (int) $request->input('tahun', now()->year);

        $requests = DtsenDataRequest::whereYear('created_at', $tahun)->get();

        $perStatus = [];
        foreach (DtsenDataRequest::statusLabels() as $key => $label) {
            $count = $requests->where('status', $key)->count();
            if ($count > 0 || in_array($key, [
                DtsenDataRequest::STATUS_DIAJUKAN,
                DtsenDataRequest::STATUS_VERIF_ADMIN,
                DtsenDataRequest::STATUS_VERIF_SUBSTANSI,
                DtsenDataRequest::STATUS_DATA_TERSEDIA,
                DtsenDataRequest::STATUS_SELESAI,
            ], true)) {
                $perStatus[$label] = $count;
            }
        }

        $perLevel = [];
        foreach (DtsenDataRequest::selectableLevels() as $level) {
            $perLevel[DtsenDataRequest::levelLabels()[$level]] = $requests->where('level_akses', $level)->count();
        }

        $perOpd = DtsenDataRequest::query()
            ->selectRaw('unit_kerja_id, count(*) as total')
            ->whereYear('created_at', $tahun)
            ->whereNotNull('unit_kerja_id')
            ->groupBy('unit_kerja_id')
            ->with('unitKerja')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $perBulan = [];
        foreach (range(1, 12) as $bulan) {
            $perBulan[$bulan] = $requests->filter(fn ($r) => $r->created_at->month === $bulan)->count();
        }

        return view('admin.dtsen.dashboard', [
            'tahun' => $tahun,
            'tahunOptions' => range(now()->year, now()->year - 4),
            'ringkasan' => [
                'permohonan' => $requests->count(),
                'berjalan' => $requests->whereNotIn('status', [
                    DtsenDataRequest::STATUS_DRAFT,
                    DtsenDataRequest::STATUS_SELESAI,
                    DtsenDataRequest::STATUS_DITOLAK,
                    DtsenDataRequest::STATUS_KEDALUWARSA,
                ])->count(),
                'ditolak' => $requests->where('status', DtsenDataRequest::STATUS_DITOLAK)->count(),
                'akun_aktif' => DtsenAccountRequest::activeAccount()->count(),
                'akun_menunggu' => DtsenAccountRequest::where('status', DtsenAccountRequest::STATUS_DIAJUKAN)->count(),
                'token_aktif' => DtsenAccessToken::whereNull('revoked_at')->where('expires_at', '>', now())->count(),
                'laporan_pemanfaatan' => DtsenUtilizationReport::whereYear('created_at', $tahun)->count(),
                'berita_pemusnahan' => DtsenDestructionReport::whereYear('created_at', $tahun)->count(),
                'insiden' => DtsenIncidentReport::whereYear('created_at', $tahun)->count(),
                'insiden_terlambat' => DtsenIncidentReport::whereYear('created_at', $tahun)->where('terlambat', true)->count(),
                'pengaduan_baru' => DtsenComplaint::where('status', DtsenComplaint::STATUS_BARU)->count(),
            ],
            'perStatus' => $perStatus,
            'perLevel' => $perLevel,
            'perOpd' => $perOpd,
            'perBulan' => $perBulan,
            'slaSummary' => $sla->summary($requests),
            'slaTotalTarget' => DtsenSlaService::totalTarget(),
            'tokenSegeraKedaluwarsa' => DtsenAccessToken::with('dataRequest.unitKerja')
                ->whereNull('revoked_at')
                ->whereBetween('expires_at', [now(), now()->addDays(DtsenAccessToken::WARN_BEFORE_DAYS)])
                ->orderBy('expires_at')
                ->get(),
            'pemusnahanTerlambat' => DtsenDestructionReport::with('dataRequest')
                ->where('status', DtsenDestructionReport::STATUS_DRAFT)
                ->whereDate('batas_penyampaian', '<', now()->toDateString())
                ->get(),
        ]);
    }
}
