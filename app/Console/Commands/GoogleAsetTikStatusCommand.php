<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleAsetTikSyncService;
use App\Services\GoogleSheetsService;
use App\Models\GoogleAsetTikSyncLog;

class GoogleAsetTikStatusCommand extends Command
{
    protected $signature = 'google-aset-tik:status';

    protected $description = 'Tampilkan status sinkronisasi Aset TIK dengan Google Spreadsheet';

    public function handle(
        GoogleAsetTikSyncService $syncService,
        GoogleSheetsService $sheetsService
    ): int {
        $this->info("📊 Status Sinkronisasi Aset TIK");
        $this->newLine();

        // Test koneksi
        $this->line("🔌 Koneksi Google Sheets API...");
        if ($sheetsService->testConnection()) {
            $this->info("   ✅ Connected");
        } else {
            $this->error("   ❌ Connection failed");
            return Command::FAILURE;
        }

        $this->newLine();

        // Get stats
        $stats = $syncService->getSyncStats();

        // Hardware stats
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("🖥️  HARDWARE (HAM-Register)");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total di Database', $stats['hardware']['total_in_db']],
                ['Pending Export', $stats['hardware']['pending_export']],
                ['Last Sync', $stats['hardware']['last_sync'] ?? 'Never'],
                ['Last Status', $stats['hardware']['last_sync_status'] ?? 'N/A'],
            ]
        );

        // Software stats
        $this->newLine();
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("💿 SOFTWARE (SAM-Register)");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total di Database', $stats['software']['total_in_db']],
                ['Pending Export', $stats['software']['pending_export']],
                ['Last Sync', $stats['software']['last_sync'] ?? 'Never'],
                ['Last Status', $stats['software']['last_sync_status'] ?? 'N/A'],
            ]
        );

        // Recent sync logs
        $this->newLine();
        $this->info("📜 Recent Sync Logs (Last 5)");
        $recentLogs = GoogleAsetTikSyncLog::latest()->take(5)->get();

        if ($recentLogs->isEmpty()) {
            $this->warn("   Belum ada sync log");
        } else {
            $this->table(
                ['ID', 'Type', 'Direction', 'Status', 'Created', 'Updated', 'Failed', 'Time'],
                $recentLogs->map(fn($log) => [
                    $log->id,
                    strtoupper($log->register_type),
                    $log->sync_type === 'import' ? '⬇️  Import' : '⬆️  Export',
                    $this->getStatusEmoji($log->status) . ' ' . $log->status,
                    $log->rows_created,
                    $log->rows_updated,
                    $log->rows_failed,
                    $log->duration ?? 'N/A',
                ])->toArray()
            );
        }

        return Command::SUCCESS;
    }

    protected function getStatusEmoji(string $status): string
    {
        return match($status) {
            'success' => '✅',
            'failed' => '❌',
            'partial' => '⚠️',
            'running' => '⏳',
            default => '❓',
        };
    }
}
