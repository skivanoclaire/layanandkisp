<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleAsetTikSyncService;
use App\Services\GoogleSheetsService;
use App\Models\GoogleAsetTikSyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class GoogleAsetTikSyncController extends Controller
{
    protected GoogleAsetTikSyncService $syncService;
    protected GoogleSheetsService $sheetsService;

    public function __construct(
        GoogleAsetTikSyncService $syncService,
        GoogleSheetsService $sheetsService
    ) {
        $this->syncService = $syncService;
        $this->sheetsService = $sheetsService;
    }

    /**
     * Halaman sync management
     */
    public function index()
    {
        $stats = $this->syncService->getSyncStats();
        $recentLogs = GoogleAsetTikSyncLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Check connection
        $isConnected = $this->sheetsService->testConnection();

        return view('admin.google-aset-tik.sync.index', compact(
            'stats',
            'recentLogs',
            'isConnected'
        ));
    }

    /**
     * Trigger import
     */
    public function import(Request $request)
    {
        $request->validate([
            'type' => 'required|in:ham,sam,kategori,all',
            'dry_run' => 'boolean',
        ]);

        try {
            // Disable session writes for long-running operation
            config(['session.driver' => 'array']);

            // Set timeout untuk operasi besar
            set_time_limit(600); // 10 menit
            ini_set('memory_limit', '6144M'); // Naikkan ke 6GB untuk data sangat besar

            // Reconnect database untuk menghindari "MySQL server has gone away"
            DB::reconnect();

            $type = $request->type;
            $dryRun = $request->boolean('dry_run', false);

            if ($type === 'all') {
                $logs = $this->syncService->importAll($dryRun);
                $message = $dryRun
                    ? 'Preview import berhasil (data tidak disimpan)'
                    : 'Import semua data berhasil';
            } else {
                $log = match($type) {
                    'ham' => $this->syncService->importHardware($dryRun),
                    'sam' => $this->syncService->importSoftware($dryRun),
                    'kategori' => $this->syncService->importKategori($dryRun),
                };

                $message = $dryRun
                    ? "Preview import {$type} berhasil"
                    : "Import {$type} berhasil";
            }

            return redirect()
                ->route('admin.google-aset-tik.sync.index')
                ->with('success', $message);

        } catch (Exception $e) {
            return redirect()
                ->route('admin.google-aset-tik.sync.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * Trigger export
     */
    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:ham,sam,all',
        ]);

        try {
            $log = $this->syncService->exportToSpreadsheet($request->type);

            return redirect()
                ->route('admin.google-aset-tik.sync.index')
                ->with('success', 'Export berhasil');

        } catch (Exception $e) {
            return redirect()
                ->route('admin.google-aset-tik.sync.index')
                ->with('error', 'Export gagal: ' . $e->getMessage());
        }
    }

    /**
     * Preview perubahan
     */
    public function preview(Request $request, string $type)
    {
        try {
            $preview = $this->syncService->previewImport($type);

            return response()->json($preview);

        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync logs history
     */
    public function logs()
    {
        $logs = GoogleAsetTikSyncLog::with('user')
            ->latest()
            ->paginate(50);

        return view('admin.google-aset-tik.sync.logs', compact('logs'));
    }

    /**
     * Test connection ke Google Sheets
     */
    public function testConnection()
    {
        try {
            $info = $this->sheetsService->getSpreadsheetInfo();

            return response()->json([
                'success' => true,
                'message' => 'Koneksi berhasil',
                'info' => $info,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi gagal: ' . $e->getMessage(),
            ], 500);
        }
    }
}
