<?php

namespace App\Services;

use App\Models\GoogleAsetTikHardware;
use App\Models\GoogleAsetTikSoftware;
use App\Models\GoogleAsetTikKategori;
use App\Models\GoogleAsetTikSyncLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleAsetTikSyncService
{
    protected GoogleSheetsService $sheets;
    protected int $batchSize;

    public function __construct(GoogleSheetsService $sheets)
    {
        $this->sheets = $sheets;
        $this->batchSize = config('google-aset-tik.sync.batch_size', 500);
    }

    /**
     * Import HAM (Hardware) dari Spreadsheet ke Database
     *
     * @param bool $dryRun Preview tanpa save ke database
     * @return GoogleAsetTikSyncLog
     */
    public function importHardware(bool $dryRun = false): GoogleAsetTikSyncLog
    {
        $log = $this->createSyncLog('ham', 'import');

        try {
            $sheetConfig = config('google-aset-tik.google.sheets.ham');

            // Fetch data in chunks directly from Google Sheets API (1000 rows at a time)
            $pageSizeFromApi = 1000; // Fetch 1000 rows per API call
            $offset = 1; // Skip header row (row 1)
            $batchIndex = 0;

            $stats = [
                'created' => 0,
                'updated' => 0,
                'failed' => 0,
                'skipped' => 0,
            ];
            $errors = [];
            $totalProcessed = 0;

            Log::info("Starting HAM import with paginated fetch");

            // Keep fetching until we get no more rows
            while (true) {
                // Fetch next page from Google Sheets
                $rows = $this->sheets->getRows($sheetConfig['name'], $sheetConfig['range'], $pageSizeFromApi, $offset);

                if (empty($rows)) {
                    break; // No more data
                }

                $pageSize = count($rows);
                Log::info("Fetched page from Google Sheets", [
                    'offset' => $offset,
                    'rows_fetched' => $pageSize,
                ]);

                // Reconnect database setiap page untuk mencegah "server has gone away"
                DB::reconnect();

                // Process rows in this page, but in smaller batches for DB operations
                $chunks = array_chunk($rows, $this->batchSize);

                foreach ($chunks as $chunkIdx => $chunk) {
                    $batchIndex++;

                    Log::info("Processing HAM batch", [
                        'batch' => $batchIndex,
                        'rows_in_batch' => count($chunk),
                    ]);

                    foreach ($chunk as $index => $row) {
                        // Calculate actual row number in spreadsheet
                        $rowNumber = $offset + ($chunkIdx * $this->batchSize) + $index + 1;

                    try {
                        // Skip baris kosong
                        if ($this->isEmptyRow($row)) {
                            $stats['skipped']++;
                            continue;
                        }

                        $data = $this->parseHardwareRow($row, $rowNumber);

                        if ($dryRun) {
                            // Dry run: hanya hitung tanpa save
                            $exists = GoogleAsetTikHardware::where('spreadsheet_row', $rowNumber)->exists();
                            $exists ? $stats['updated']++ : $stats['created']++;
                        } else {
                            // Actual import with retry on connection error
                            try {
                                $asset = GoogleAsetTikHardware::updateOrCreate(
                                    ['spreadsheet_row' => $rowNumber],
                                    $data
                                );

                                $asset->wasRecentlyCreated ? $stats['created']++ : $stats['updated']++;
                            } catch (\Illuminate\Database\QueryException $e) {
                                // Retry once on connection error
                                if (str_contains($e->getMessage(), 'server has gone away')) {
                                    DB::reconnect();
                                    $asset = GoogleAsetTikHardware::updateOrCreate(
                                        ['spreadsheet_row' => $rowNumber],
                                        $data
                                    );
                                    $asset->wasRecentlyCreated ? $stats['created']++ : $stats['updated']++;
                                } else {
                                    throw $e;
                                }
                            }
                        }

                    } catch (Exception $e) {
                        $stats['failed']++;
                        $errors[] = [
                            'row' => $rowNumber,
                            'error' => $e->getMessage(),
                        ];

                        Log::warning("Error importing HAM row {$rowNumber}", [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                    // Free memory setelah setiap batch
                    unset($chunk);
                    gc_collect_cycles();
                }

                // Update total processed and move to next page
                $totalProcessed += $pageSize;
                $offset += $pageSizeFromApi;

                // Free memory from rows
                unset($rows);
                unset($chunks);
                gc_collect_cycles();

                // Break if we got less than page size (end of data)
                if ($pageSize < $pageSizeFromApi) {
                    break;
                }
            }

            // Update log with final stats
            $log->update([
                'total_rows' => $totalProcessed,
                'status' => $stats['failed'] > 0 ? 'partial' : 'success',
                'rows_created' => $stats['created'],
                'rows_updated' => $stats['updated'],
                'rows_failed' => $stats['failed'],
                'rows_skipped' => $stats['skipped'],
                'error_details' => !empty($errors) ? $errors : null,
                'completed_at' => now(),
            ]);

            Log::info("HAM import completed", [
                'dry_run' => $dryRun,
                'stats' => $stats,
            ]);

        } catch (Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            Log::error("HAM import failed", [
                'error' => $e->getMessage(),
            ]);
        }

        return $log->fresh();
    }

    /**
     * Import SAM (Software) dari Spreadsheet ke Database
     *
     * @param bool $dryRun
     * @return GoogleAsetTikSyncLog
     */
    public function importSoftware(bool $dryRun = false): GoogleAsetTikSyncLog
    {
        $log = $this->createSyncLog('sam', 'import');

        try {
            $sheetConfig = config('google-aset-tik.google.sheets.sam');
            $rows = $this->sheets->getRows($sheetConfig['name'], $sheetConfig['range']);

            // Skip header
            $header = array_shift($rows);
            $totalRows = count($rows);
            $log->update(['total_rows' => $totalRows]);

            $stats = [
                'created' => 0,
                'updated' => 0,
                'failed' => 0,
                'skipped' => 0,
            ];
            $errors = [];

            $chunks = array_chunk($rows, $this->batchSize);

            // Free memory dari $rows original
            unset($rows);
            gc_collect_cycles();

            foreach ($chunks as $chunkIndex => $chunk) {
                // Reconnect database setiap batch untuk mencegah "server has gone away"
                DB::reconnect();

                foreach ($chunk as $index => $row) {
                    $rowNumber = ($chunkIndex * $this->batchSize) + $index + 2;

                    try {
                        if ($this->isEmptyRow($row)) {
                            $stats['skipped']++;
                            continue;
                        }

                        $data = $this->parseSoftwareRow($row, $rowNumber);

                        if ($dryRun) {
                            $exists = GoogleAsetTikSoftware::where('spreadsheet_row', $rowNumber)->exists();
                            $exists ? $stats['updated']++ : $stats['created']++;
                        } else {
                            // Actual import with retry on connection error
                            try {
                                $asset = GoogleAsetTikSoftware::updateOrCreate(
                                    ['spreadsheet_row' => $rowNumber],
                                    $data
                                );

                                $asset->wasRecentlyCreated ? $stats['created']++ : $stats['updated']++;
                            } catch (\Illuminate\Database\QueryException $e) {
                                // Retry once on connection error
                                if (str_contains($e->getMessage(), 'server has gone away')) {
                                    DB::reconnect();
                                    $asset = GoogleAsetTikSoftware::updateOrCreate(
                                        ['spreadsheet_row' => $rowNumber],
                                        $data
                                    );
                                    $asset->wasRecentlyCreated ? $stats['created']++ : $stats['updated']++;
                                } else {
                                    throw $e;
                                }
                            }
                        }

                    } catch (Exception $e) {
                        $stats['failed']++;
                        $errors[] = [
                            'row' => $rowNumber,
                            'error' => $e->getMessage(),
                        ];
                    }
                }

                // Free memory setelah setiap batch
                unset($chunk);
                gc_collect_cycles();
            }

            $log->update([
                'status' => $stats['failed'] > 0 ? 'partial' : 'success',
                'rows_created' => $stats['created'],
                'rows_updated' => $stats['updated'],
                'rows_failed' => $stats['failed'],
                'rows_skipped' => $stats['skipped'],
                'error_details' => !empty($errors) ? $errors : null,
                'completed_at' => now(),
            ]);

        } catch (Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }

        return $log->fresh();
    }

    /**
     * Import kategori dari Spreadsheet
     */
    public function importKategori(bool $dryRun = false): GoogleAsetTikSyncLog
    {
        $log = $this->createSyncLog('kategori', 'import');

        try {
            $sheetConfig = config('google-aset-tik.google.sheets.kategori');
            $rows = $this->sheets->getRows($sheetConfig['name'], $sheetConfig['range']);

            // Skip header
            $header = array_shift($rows);
            $log->update(['total_rows' => count($rows)]);

            $stats = ['created' => 0, 'updated' => 0, 'failed' => 0, 'skipped' => 0];

            foreach ($rows as $row) {
                try {
                    if (count($row) < 2 || empty($row[0])) {
                        $stats['skipped']++;
                        continue;
                    }

                    if (!$dryRun) {
                        $kategori = GoogleAsetTikKategori::updateOrCreate(
                            ['nama_perangkat' => $row[0]],
                            ['kategori_perangkat' => $row[1] ?? '']
                        );

                        $kategori->wasRecentlyCreated ? $stats['created']++ : $stats['updated']++;
                    }

                } catch (Exception $e) {
                    $stats['failed']++;
                }
            }

            $log->update([
                'status' => 'success',
                'rows_created' => $stats['created'],
                'rows_updated' => $stats['updated'],
                'rows_failed' => $stats['failed'],
                'rows_skipped' => $stats['skipped'],
                'completed_at' => now(),
            ]);

        } catch (Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }

        return $log->fresh();
    }

    /**
     * Import semua data (HAM + SAM + Kategori)
     *
     * @param bool $dryRun
     * @return array Array of sync logs
     */
    public function importAll(bool $dryRun = false): array
    {
        Log::info('Starting full import', ['dry_run' => $dryRun]);

        return [
            'kategori' => $this->importKategori($dryRun),
            'ham' => $this->importHardware($dryRun),
            'sam' => $this->importSoftware($dryRun),
        ];
    }

    /**
     * Export data yang pending ke Spreadsheet
     *
     * @param string $type 'ham', 'sam', atau 'all'
     * @return GoogleAsetTikSyncLog
     */
    public function exportToSpreadsheet(string $type = 'all'): GoogleAsetTikSyncLog
    {
        // TODO: Implementasi export
        // Ambil data dengan status 'pending_export'
        // Update spreadsheet row by row atau batch
        // Mark as synced setelah berhasil

        $log = $this->createSyncLog($type, 'export');
        $log->update([
            'status' => 'success',
            'error_message' => 'Export feature belum diimplementasikan',
            'completed_at' => now(),
        ]);

        return $log;
    }

    /**
     * Preview perubahan sebelum import
     *
     * @param string $type 'ham' atau 'sam'
     * @return array Summary of changes
     */
    public function previewImport(string $type): array
    {
        if ($type === 'ham') {
            $log = $this->importHardware(dryRun: true);
        } else {
            $log = $this->importSoftware(dryRun: true);
        }

        return [
            'type' => $type,
            'total_rows' => $log->total_rows,
            'will_create' => $log->rows_created,
            'will_update' => $log->rows_updated,
            'will_skip' => $log->rows_skipped,
            'errors' => $log->error_details ?? [],
        ];
    }

    /**
     * Get sync statistics
     */
    public function getSyncStats(): array
    {
        $lastHamSync = GoogleAsetTikSyncLog::where('register_type', 'ham')
            ->where('sync_type', 'import')
            ->latest()
            ->first();

        $lastSamSync = GoogleAsetTikSyncLog::where('register_type', 'sam')
            ->where('sync_type', 'import')
            ->latest()
            ->first();

        return [
            'hardware' => [
                'total_in_db' => GoogleAsetTikHardware::count(),
                'pending_export' => GoogleAsetTikHardware::pendingExport()->count(),
                'last_sync' => $lastHamSync?->synced_at,
                'last_sync_status' => $lastHamSync?->status,
            ],
            'software' => [
                'total_in_db' => GoogleAsetTikSoftware::count(),
                'pending_export' => GoogleAsetTikSoftware::pendingExport()->count(),
                'last_sync' => $lastSamSync?->synced_at,
                'last_sync_status' => $lastSamSync?->status,
            ],
        ];
    }

    // ==================== HELPER METHODS ====================

    /**
     * Parse row data HAM dari spreadsheet ke format database
     */
    protected function parseHardwareRow(array $row, int $rowNumber): array
    {
        return [
            'no' => $this->parseInt($row[0] ?? null),
            'nama_opd' => $row[1] ?? null,
            'nama_aset' => $row[2] ?? null,
            'kode_gab_barang' => $row[3] ?? null,
            'no_register' => $row[4] ?? null,
            'total' => $this->parseInt($row[5]) ?? 1,
            'merk_type' => $row[6] ?? null,
            'tahun' => $this->parseInt($row[7] ?? null),
            'nilai_perolehan' => $this->parseNumber($row[8] ?? 0),
            'jenis_aset_tik' => $row[9] ?? null,
            'sumber_pendanaan' => $row[10] ?? null,
            'keadaan_barang' => $row[11] ?? null,
            'tanggal_perolehan' => $this->parseInt($row[12] ?? null), // year only
            'tanggal_penyerahan' => $this->parseDate($row[13] ?? null),
            'asal_usul' => $row[14] ?? null,
            'status' => $row[15] ?? null,
            'terotorisasi' => $row[16] ?? null,
            'aset_vital' => $row[17] ?? null,
            'keterangan' => $row[18] ?? null,
            'spreadsheet_row' => $rowNumber,
            'synced_at' => now(),
            'sync_status' => 'synced',
        ];
    }

    /**
     * Parse row data SAM dari spreadsheet ke format database
     */
    protected function parseSoftwareRow(array $row, int $rowNumber): array
    {
        return [
            'no' => $this->parseInt($row[0] ?? null),
            'nama_opd' => $row[1] ?? null,
            'nama_aset' => $row[2] ?? null,
            'kode_barang' => $row[3] ?? null,
            'tahun' => $this->parseInt($row[4] ?? null),
            'judul' => $row[5] ?? null,
            'harga' => $this->parseNumber($row[6] ?? 0),
            'is_aktif' => $row[7] ?? null,
            'keterangan_software' => $row[8] ?? null,
            'jenis_perangkat_lunak' => $row[9] ?? null,
            'data_output' => $row[10] ?? null,
            'pengembangan' => $row[11] ?? null,
            'sewa' => $row[12] ?? null,
            'software_berjalan' => $row[13] ?? null,
            'fitur_sesuai' => $row[14] ?? null,
            'url' => $row[15] ?? null,
            'integrasi' => $row[16] ?? null,
            'platform' => $row[17] ?? null,
            'database' => $row[18] ?? null,
            'script' => $row[19] ?? null,
            'framework' => $row[20] ?? null,
            'status' => $row[21] ?? null,
            'terotorisasi' => $row[22] ?? null,
            'aset_vital' => $row[23] ?? null,
            'keterangan_utilisasi' => $row[24] ?? null,
            'asal_usul' => $row[25] ?? null,
            'spreadsheet_row' => $rowNumber,
            'synced_at' => now(),
            'sync_status' => 'synced',
        ];
    }

    /**
     * Parse angka dari format spreadsheet
     * Mendukung format:
     * - 1028500 (plain number)
     * - 1,028,500 (US format - comma as thousand separator)
     * - 1.028.500 (ID format - dot as thousand separator)
     * - 1028500.50 atau 1,028,500.50 (dengan desimal)
     */
    protected function parseNumber($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Convert to string and trim
        $value = trim((string) $value);

        if (empty($value)) {
            return 0;
        }

        // Count dots and commas to determine format
        $dotCount = substr_count($value, '.');
        $commaCount = substr_count($value, ',');

        if ($commaCount > 0 && $dotCount > 0) {
            // Mixed format: check which comes last (that's the decimal separator)
            $lastDot = strrpos($value, '.');
            $lastComma = strrpos($value, ',');

            if ($lastDot > $lastComma) {
                // Format: 1,028,500.50 (US format - comma thousand, dot decimal)
                $cleaned = str_replace(',', '', $value);
            } else {
                // Format: 1.028.500,50 (EU format - dot thousand, comma decimal)
                $cleaned = str_replace('.', '', $value);
                $cleaned = str_replace(',', '.', $cleaned);
            }
        } elseif ($commaCount > 1) {
            // Multiple commas = thousand separator, format: 1,028,500
            $cleaned = str_replace(',', '', $value);
        } elseif ($dotCount > 1) {
            // Multiple dots = thousand separator, format: 1.028.500
            $cleaned = str_replace('.', '', $value);
        } elseif ($commaCount === 1) {
            // Single comma - could be thousand separator (1,028) or decimal (10,5)
            // Check position: if comma is in last 3 chars, likely thousand separator
            $commaPos = strpos($value, ',');
            $afterComma = strlen($value) - $commaPos - 1;

            if ($afterComma === 3 && $dotCount === 0) {
                // Format: 1,028 (thousand separator)
                $cleaned = str_replace(',', '', $value);
            } else {
                // Format: 10,5 (decimal separator - European)
                $cleaned = str_replace(',', '.', $value);
            }
        } elseif ($dotCount === 1) {
            // Single dot - likely decimal: 1028.50
            $cleaned = $value;
        } else {
            // No separators - plain number
            $cleaned = $value;
        }

        return (float) $cleaned;
    }

    /**
     * Parse integer dengan null handling
     */
    protected function parseInt($value): ?int
    {
        if (empty($value) || !is_numeric($value)) {
            return null;
        }
        return (int) $value;
    }

    /**
     * Parse tanggal dari format spreadsheet
     */
    protected function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Jika sudah format tanggal valid
        try {
            return date('Y-m-d', strtotime($value));
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Check apakah row kosong
     */
    protected function isEmptyRow(array $row): bool
    {
        // Row dianggap kosong jika semua kolom kosong atau hanya kolom pertama yang isi
        $nonEmpty = array_filter($row, fn($cell) => !empty($cell));
        return count($nonEmpty) === 0;
    }

    /**
     * Create sync log entry
     */
    protected function createSyncLog(string $registerType, string $syncType): GoogleAsetTikSyncLog
    {
        return GoogleAsetTikSyncLog::create([
            'register_type' => $registerType,
            'sync_type' => $syncType,
            'status' => 'running',
            'started_at' => now(),
            'user_id' => auth()->id(),
            'is_manual' => true,
        ]);
    }
}
