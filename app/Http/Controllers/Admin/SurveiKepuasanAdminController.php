<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveiKepuasanLayanan;
use App\Models\WebMonitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SurveiKepuasanExport;

class SurveiKepuasanAdminController extends Controller
{
    /**
     * Display a listing of all surveys with statistics
     */
    public function index(Request $request)
    {
        $query = SurveiKepuasanLayanan::with(['user', 'webMonitor']);

        // Filter by subdomain
        if ($request->filled('subdomain')) {
            $query->where('web_monitor_id', $request->subdomain);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $surveys = $query->latest()->paginate(20);

        // Get all subdomains for filter
        $webMonitors = WebMonitor::whereNotNull('subdomain')
            ->orderBy('subdomain')
            ->get();

        // Calculate overall statistics
        $allSurveys = SurveiKepuasanLayanan::all();
        $stats = [
            'total_surveys' => $allSurveys->count(),
            'avg_rating' => $allSurveys->isEmpty() ? 0 : round($allSurveys->avg(function($survey) {
                return $survey->average_rating;
            }), 2),
            'total_subdomains_surveyed' => SurveiKepuasanLayanan::distinct('web_monitor_id')->count('web_monitor_id'),
            'total_respondents' => SurveiKepuasanLayanan::distinct('user_id')->count('user_id'),
        ];

        // Rating distribution
        $ratingDistribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingDistribution[$i] = SurveiKepuasanLayanan::where('rating_keseluruhan', $i)->count();
        }

        return view('admin.survei-kepuasan.index', compact('surveys', 'webMonitors', 'stats', 'ratingDistribution'));
    }

    /**
     * Display detailed view of a survey
     */
    public function show($id)
    {
        $survey = SurveiKepuasanLayanan::with(['user', 'webMonitor'])->findOrFail($id);

        return view('admin.survei-kepuasan.show', compact('survey'));
    }

    /**
     * Show statistics and analysis
     */
    public function statistics()
    {
        // Average ratings per category
        $avgRatings = [
            'kecepatan' => round(SurveiKepuasanLayanan::avg('rating_kecepatan'), 2),
            'kemudahan' => round(SurveiKepuasanLayanan::avg('rating_kemudahan'), 2),
            'kualitas' => round(SurveiKepuasanLayanan::avg('rating_kualitas'), 2),
            'responsif' => round(SurveiKepuasanLayanan::avg('rating_responsif'), 2),
            'keamanan' => round(SurveiKepuasanLayanan::avg('rating_keamanan'), 2),
            'keseluruhan' => round(SurveiKepuasanLayanan::avg('rating_keseluruhan'), 2),
        ];

        // Top rated subdomains
        $topSubdomains = SurveiKepuasanLayanan::select('web_monitor_id')
            ->selectRaw('COUNT(*) as survey_count')
            ->selectRaw('AVG((rating_kecepatan + rating_kemudahan + rating_kualitas +
                        rating_responsif + rating_keamanan + rating_keseluruhan) / 6) as avg_rating')
            ->with('webMonitor')
            ->groupBy('web_monitor_id')
            ->having('survey_count', '>=', 1)
            ->orderByDesc('avg_rating')
            ->limit(10)
            ->get();

        // Recent feedback
        $recentFeedback = SurveiKepuasanLayanan::with(['user', 'webMonitor'])
            ->whereNotNull('saran')
            ->orWhereNotNull('kelebihan')
            ->orWhereNotNull('kekurangan')
            ->latest()
            ->limit(10)
            ->get();

        // Monthly trend (last 6 months)
        $monthlyTrend = SurveiKepuasanLayanan::selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as count,
                AVG((rating_kecepatan + rating_kemudahan + rating_kualitas +
                    rating_responsif + rating_keamanan + rating_keseluruhan) / 6) as avg_rating
            ')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('year DESC, month DESC')
            ->get();

        return view('admin.survei-kepuasan.statistics', compact(
            'avgRatings',
            'topSubdomains',
            'recentFeedback',
            'monthlyTrend'
        ));
    }

    /**
     * Export survey data to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = SurveiKepuasanLayanan::with(['user', 'webMonitor']);

        // Apply filters
        if ($request->filled('subdomain')) {
            $query->where('web_monitor_id', $request->subdomain);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $surveys = $query->latest()->get();

        // Calculate statistics
        $stats = [
            'total_surveys' => $surveys->count(),
            'avg_rating' => $surveys->isEmpty() ? 0 : round($surveys->avg(function($survey) {
                return $survey->average_rating;
            }), 2),
            'avg_kecepatan' => $surveys->isEmpty() ? 0 : round($surveys->avg('rating_kecepatan'), 2),
            'avg_kemudahan' => $surveys->isEmpty() ? 0 : round($surveys->avg('rating_kemudahan'), 2),
            'avg_kualitas' => $surveys->isEmpty() ? 0 : round($surveys->avg('rating_kualitas'), 2),
            'avg_responsif' => $surveys->isEmpty() ? 0 : round($surveys->avg('rating_responsif'), 2),
            'avg_keamanan' => $surveys->isEmpty() ? 0 : round($surveys->avg('rating_keamanan'), 2),
            'avg_keseluruhan' => $surveys->isEmpty() ? 0 : round($surveys->avg('rating_keseluruhan'), 2),
        ];

        $filters = [
            'subdomain' => $request->subdomain ? WebMonitor::find($request->subdomain)?->subdomain : 'Semua Subdomain',
            'date_from' => $request->date_from ?? 'Awal',
            'date_to' => $request->date_to ?? 'Sekarang',
        ];

        // Render view
        $html = view('admin.survei-kepuasan.export-pdf', compact('surveys', 'stats', 'filters'))->render();

        // Setup Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="laporan-survei-kepuasan-' . date('Y-m-d') . '.pdf"');
    }

    /**
     * Export survey data to Excel
     */
    public function exportExcel(Request $request)
    {
        $filters = [
            'subdomain' => $request->subdomain,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ];

        return Excel::download(
            new SurveiKepuasanExport($filters),
            'data-survei-kepuasan-' . date('Y-m-d') . '.xlsx'
        );
    }
}
