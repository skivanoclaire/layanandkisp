<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekomendasiAplikasiForm;
use App\Models\RekomendasiHistoriAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekomendasiMonitoringController extends Controller
{
    /**
     * Display the monitoring dashboard.
     */
    public function dashboard(Request $request)
    {
        // Statistics
        $stats = [
            'total' => RekomendasiAplikasiForm::count(),
            'draft' => RekomendasiAplikasiForm::where('status', 'draft')->count(),
            'menunggu_verifikasi' => RekomendasiAplikasiForm::where('status', 'menunggu_verifikasi')->count(),
            'disetujui' => RekomendasiAplikasiForm::where('status', 'disetujui')->count(),
            'ditolak' => RekomendasiAplikasiForm::where('status', 'ditolak')->count(),
            'perlu_revisi' => RekomendasiAplikasiForm::where('status', 'perlu_revisi')->count(),
        ];

        // Phase statistics
        $phaseStats = [
            'usulan' => RekomendasiAplikasiForm::where('fase_saat_ini', 'usulan')->count(),
            'verifikasi' => RekomendasiAplikasiForm::where('fase_saat_ini', 'verifikasi')->count(),
            'penandatanganan' => RekomendasiAplikasiForm::where('fase_saat_ini', 'penandatanganan')->count(),
            'menunggu_kementerian' => RekomendasiAplikasiForm::where('fase_saat_ini', 'menunggu_kementerian')->count(),
            'pengembangan' => RekomendasiAplikasiForm::where('fase_saat_ini', 'pengembangan')->count(),
            'selesai' => RekomendasiAplikasiForm::where('fase_saat_ini', 'selesai')->count(),
        ];

        // Monthly trend (last 6 months)
        $monthlyData = RekomendasiAplikasiForm::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subMonths(6))
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get();

        // Recent applications
        $query = RekomendasiAplikasiForm::with([
            'user.unitKerja',
            'pemilikProsesBisnis',
            'verifikasi',
            'surat.statusKementerian'
        ])->latest();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('fase')) {
            $query->where('fase_saat_ini', $request->fase);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_aplikasi', 'like', '%' . $request->search . '%')
                  ->orWhere('ticket_number', 'like', '%' . $request->search . '%');
            });
        }

        $applications = $query->paginate(15);

        // Recent activities
        $recentActivities = RekomendasiHistoriAktivitas::with(['proposal', 'user'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.rekomendasi.monitoring.dashboard', compact(
            'stats',
            'phaseStats',
            'monthlyData',
            'applications',
            'recentActivities'
        ));
    }

    /**
     * Filter by phase.
     */
    public function byPhase($fase)
    {
        $applications = RekomendasiAplikasiForm::where('fase_saat_ini', $fase)
            ->with(['user.unitKerja', 'pemilikProsesBisnis', 'verifikasi', 'surat.statusKementerian'])
            ->latest()
            ->paginate(15);

        return view('admin.rekomendasi.monitoring.by-phase', compact('applications', 'fase'));
    }

    /**
     * Filter by status.
     */
    public function byStatus($status)
    {
        $applications = RekomendasiAplikasiForm::where('status', $status)
            ->with(['user.unitKerja', 'pemilikProsesBisnis', 'verifikasi', 'surat.statusKementerian'])
            ->latest()
            ->paginate(15);

        return view('admin.rekomendasi.monitoring.by-status', compact('applications', 'status'));
    }

    /**
     * View detailed history of an application.
     */
    public function history($id)
    {
        $proposal = RekomendasiAplikasiForm::with([
            'user.unitKerja',
            'pemilikProsesBisnis',
            'verifikasi.verifikator',
            'surat.statusKementerian',
            'fasePengembangan.dokumenPengembangan',
            'fasePengembangan.milestones',
            'timPengembangan',
            'evaluasi',
            'historiAktivitas.user'
        ])->findOrFail($id);

        return view('admin.rekomendasi.monitoring.history', compact('proposal'));
    }

    /**
     * Export to Excel.
     */
    public function exportExcel(Request $request)
    {
        $query = RekomendasiAplikasiForm::with([
            'user.unitKerja',
            'pemilikProsesBisnis',
            'verifikasi',
            'surat.statusKementerian'
        ]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('fase')) {
            $query->where('fase_saat_ini', $request->fase);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $applications = $query->get();

        // Create Excel file using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nomor Tiket');
        $sheet->setCellValue('C1', 'Nama Aplikasi');
        $sheet->setCellValue('D1', 'Pemohon');
        $sheet->setCellValue('E1', 'Unit Kerja');
        $sheet->setCellValue('F1', 'Status');
        $sheet->setCellValue('G1', 'Fase');
        $sheet->setCellValue('H1', 'Prioritas');
        $sheet->setCellValue('I1', 'Tanggal Dibuat');
        $sheet->setCellValue('J1', 'Status Kementerian');

        // Style headers
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle('A1:J1')->getFont()->getColor()->setARGB('FFFFFFFF');

        // Data
        $row = 2;
        foreach ($applications as $index => $app) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $app->ticket_number);
            $sheet->setCellValue('C' . $row, $app->nama_aplikasi);
            $sheet->setCellValue('D' . $row, $app->user?->name ?? 'N/A');
            $sheet->setCellValue('E' . $row, $app->pemilikProsesBisnis?->nama ?? $app->user?->unitKerja?->nama ?? 'N/A');
            $sheet->setCellValue('F' . $row, ucfirst(str_replace('_', ' ', $app->status)));
            $sheet->setCellValue('G' . $row, ucfirst(str_replace('_', ' ', $app->fase_saat_ini)));
            $sheet->setCellValue('H' . $row, ucfirst(str_replace('_', ' ', $app->prioritas ?? 'normal')));
            $sheet->setCellValue('I' . $row, $app->created_at->format('d/m/Y'));

            $statusKementerian = 'Belum Ada';
            if ($app->surat && $app->surat->statusKementerian) {
                $statusKementerian = ucfirst(str_replace('_', ' ', $app->surat->statusKementerian->status));
            }
            $sheet->setCellValue('J' . $row, $statusKementerian);

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generate file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'rekomendasi_aplikasi_' . date('YmdHis') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Export to PDF.
     */
    public function exportPDF(Request $request)
    {
        $query = RekomendasiAplikasiForm::with([
            'user.unitKerja',
            'pemilikProsesBisnis',
            'verifikasi',
            'surat.statusKementerian'
        ]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('fase')) {
            $query->where('fase_saat_ini', $request->fase);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $applications = $query->get();

        $pdf = \PDF::loadView('admin.rekomendasi.monitoring.export-pdf', compact('applications'));

        return $pdf->download('rekomendasi_aplikasi_' . date('YmdHis') . '.pdf');
    }

    /**
     * Get chart data for dashboard.
     */
    public function getChartData()
    {
        // Status distribution
        $statusData = RekomendasiAplikasiForm::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Phase distribution
        $phaseData = RekomendasiAplikasiForm::select('fase_saat_ini', DB::raw('count(*) as count'))
            ->groupBy('fase_saat_ini')
            ->get()
            ->pluck('count', 'fase_saat_ini');

        // Monthly trend (last 12 months)
        $monthlyTrend = RekomendasiAplikasiForm::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get();

        return response()->json([
            'status' => $statusData,
            'phase' => $phaseData,
            'monthly' => $monthlyTrend,
        ]);
    }
}
