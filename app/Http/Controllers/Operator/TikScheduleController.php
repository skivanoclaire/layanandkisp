<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\VidconData;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TikScheduleController extends Controller
{
    public function schedule(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));

        // Get all vidcon data for the selected month
        $schedules = VidconData::with('operators')
            ->whereYear('tanggal_mulai', $year)
            ->whereMonth('tanggal_mulai', $month)
            ->orderBy('tanggal_mulai', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        // Get upcoming schedules (next 7 days)
        $upcomingSchedules = VidconData::with('operators')
            ->where('tanggal_mulai', '>=', Carbon::today())
            ->where('tanggal_mulai', '<=', Carbon::today()->addDays(7))
            ->orderBy('tanggal_mulai', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        return view('operator.tik.schedule', compact('schedules', 'upcomingSchedules', 'year', 'month'));
    }

    public function show(VidconData $vidconData)
    {
        // Eager load relationships
        $vidconData->load(['unitKerja', 'operators']);

        return view('operator.tik.show', compact('vidconData'));
    }

    public function statistic(Request $request)
    {
        $year = $request->get('year', date('Y'));

        // Total vidcon per bulan
        $monthlyStats = VidconData::whereYear('tanggal_mulai', $year)
            ->selectRaw('MONTH(tanggal_mulai) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');

        // Total per platform
        $platformStats = VidconData::whereYear('tanggal_mulai', $year)
            ->selectRaw('platform, COUNT(*) as total')
            ->whereNotNull('platform')
            ->groupBy('platform')
            ->orderByDesc('total')
            ->get();

        // Total per unit kerja (top 10)
        $instansiStats = VidconData::with('unitKerja')
            ->whereYear('tanggal_mulai', $year)
            ->whereNotNull('unit_kerja_id')
            ->get()
            ->groupBy('unit_kerja_id')
            ->map(function ($items) {
                $unitKerja = $items->first()->unitKerja;
                return (object) [
                    'nama_instansi' => $unitKerja ? $unitKerja->nama : 'Instansi Tidak Diketahui',
                    'total' => $items->count()
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        // Total per operator using pivot table
        $operatorStats = \DB::table('operator_vidcon_data')
            ->join('vidcon_data', 'operator_vidcon_data.vidcon_data_id', '=', 'vidcon_data.id')
            ->join('users', 'operator_vidcon_data.user_id', '=', 'users.id')
            ->whereYear('vidcon_data.tanggal_mulai', $year)
            ->selectRaw('users.name as operator, COUNT(DISTINCT operator_vidcon_data.vidcon_data_id) as total')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->get();

        // Total keseluruhan
        $totalVidcon = VidconData::whereYear('tanggal_mulai', $year)->count();

        // Prepare monthly data for chart (fill missing months with 0)
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[$i] = $monthlyStats->get($i, 0);
        }

        return view('operator.tik.statistic', compact(
            'monthlyData',
            'platformStats',
            'instansiStats',
            'operatorStats',
            'totalVidcon',
            'year'
        ));
    }
}
