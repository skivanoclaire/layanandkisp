<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleAsetTikSyncService;

class ExportGoogleAsetTikCommand extends Command
{
    protected $signature = 'google-aset-tik:export
                            {type=all : Tipe data (ham, sam, all)}
                            {--dry-run : Preview tanpa update spreadsheet}';

    protected $description = 'Export data Aset TIK yang pending ke Google Spreadsheet';

    public function handle(GoogleAsetTikSyncService $syncService): int
    {
        $type = $this->argument('type');

        $this->info("📤 Export Aset TIK ke Google Spreadsheet");
        $this->info("Tipe: {$type}");

        // TODO: Implementasi export
        $this->warn("Feature belum diimplementasikan");

        return Command::SUCCESS;
    }
}
