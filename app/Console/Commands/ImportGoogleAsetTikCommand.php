<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleAsetTikSyncService;
use Exception;

class ImportGoogleAsetTikCommand extends Command
{
    protected $signature = 'google-aset-tik:import
                            {type=all : Tipe data (ham, sam, kategori, all)}
                            {--dry-run : Preview tanpa save ke database}
                            {--force : Skip konfirmasi}';

    protected $description = 'Import data Aset TIK dari Google Spreadsheet';

    public function handle(GoogleAsetTikSyncService $syncService): int
    {
        $type = $this->argument('type');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info("🔄 Import Aset TIK dari Google Spreadsheet");
        $this->info("Tipe: {$type}");

        if ($dryRun) {
            $this->warn("⚠️  DRY RUN MODE - Data tidak akan disimpan");
        }

        // Konfirmasi
        if (!$force && !$dryRun) {
            if (!$this->confirm('Lanjutkan import? Data existing akan di-update.')) {
                $this->info('Import dibatalkan.');
                return Command::SUCCESS;
            }
        }

        try {
            if ($type === 'all') {
                $logs = $syncService->importAll($dryRun);

                foreach ($logs as $logType => $log) {
                    $this->displaySyncResult($logType, $log);
                }
            } else {
                $log = match($type) {
                    'ham' => $syncService->importHardware($dryRun),
                    'sam' => $syncService->importSoftware($dryRun),
                    'kategori' => $syncService->importKategori($dryRun),
                    default => throw new Exception("Tipe tidak valid: {$type}"),
                };

                $this->displaySyncResult($type, $log);
            }

            $this->newLine();
            $this->info('✅ Import selesai!');

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error("❌ Import gagal: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function displaySyncResult(string $type, $log): void
    {
        $this->newLine();
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 Hasil Import: " . strtoupper($type));
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Rows', $log->total_rows],
                ['Created', $log->rows_created],
                ['Updated', $log->rows_updated],
                ['Failed', $log->rows_failed],
                ['Skipped', $log->rows_skipped],
                ['Status', strtoupper($log->status)],
                ['Duration', $log->duration ?? 'N/A'],
            ]
        );

        if ($log->error_details) {
            $this->warn("⚠️  {$log->rows_failed} baris gagal diimport");
            $this->line("Lihat detail error di sync log ID: {$log->id}");
        }
    }
}
