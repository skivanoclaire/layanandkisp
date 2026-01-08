<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleSheetsService
{
    protected Client $client;
    protected Sheets $service;
    protected string $spreadsheetId;

    public function __construct()
    {
        $this->initializeClient();
        $this->spreadsheetId = config('google-aset-tik.google.spreadsheet_id');

        if (!$this->spreadsheetId) {
            throw new Exception('Google Sheets Spreadsheet ID tidak dikonfigurasi');
        }
    }

    /**
     * Initialize Google Client dengan Service Account
     */
    protected function initializeClient(): void
    {
        $credentialsPath = config('google-aset-tik.google.credentials_path');

        if (!$credentialsPath || !file_exists(storage_path($credentialsPath))) {
            throw new Exception('Google Service Account credentials tidak ditemukan');
        }

        try {
            $this->client = new Client();
            $this->client->setApplicationName('Layanan Aset TIK Kaltara');
            $this->client->setScopes([Sheets::SPREADSHEETS]);
            $this->client->setAuthConfig(storage_path($credentialsPath));
            $this->client->setAccessType('offline');

            $this->service = new Sheets($this->client);

            Log::info('Google Sheets API initialized successfully');
        } catch (Exception $e) {
            Log::error('Failed to initialize Google Sheets API', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get all rows dari sheet tertentu
     *
     * @param string $sheetName Nama sheet (contoh: 'HAM-Register')
     * @param string $range Range kolom (contoh: 'A:S')
     * @param int|null $limit Maximum rows to fetch (null = all)
     * @param int $offset Start from row number (default 0)
     * @return array Array of rows
     */
    public function getRows(string $sheetName, string $range = '', ?int $limit = null, int $offset = 0): array
    {
        try {
            // If limit specified, modify range to include row numbers
            if ($limit !== null) {
                // Extract column range (e.g., 'A:S' from range)
                $startRow = $offset + 1; // +1 because sheets are 1-indexed
                $endRow = $offset + $limit;

                if ($range) {
                    // Range like 'A:S' becomes 'A1:S1000'
                    $fullRange = "{$sheetName}!{$range}{$startRow}:{$range}{$endRow}";
                    // Fix: 'A:S1:A:S1000' is wrong, need to extract just column letters
                    preg_match('/([A-Z]+):([A-Z]+)/', $range, $matches);
                    if ($matches) {
                        $startCol = $matches[1];
                        $endCol = $matches[2];
                        $fullRange = "{$sheetName}!{$startCol}{$startRow}:{$endCol}{$endRow}";
                    } else {
                        $fullRange = "{$sheetName}!{$range}";
                    }
                } else {
                    $fullRange = "{$sheetName}!{$startRow}:{$endRow}";
                }
            } else {
                $fullRange = $range ? "{$sheetName}!{$range}" : $sheetName;
            }

            Log::info('Fetching rows from Google Sheets', [
                'sheet' => $sheetName,
                'range' => $fullRange,
                'limit' => $limit,
                'offset' => $offset,
            ]);

            $response = $this->service->spreadsheets_values->get(
                $this->spreadsheetId,
                $fullRange
            );

            $values = $response->getValues();

            Log::info('Rows fetched successfully', [
                'count' => count($values ?? []),
            ]);

            return $values ?? [];

        } catch (Exception $e) {
            Log::error('Error fetching rows from Google Sheets', [
                'sheet' => $sheetName,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get rows dengan header sebagai array key
     *
     * @param string $sheetName
     * @param string $range
     * @return array Array of associative arrays
     */
    public function getRowsWithHeaders(string $sheetName, string $range = ''): array
    {
        $rows = $this->getRows($sheetName, $range);

        if (empty($rows)) {
            return [];
        }

        // Baris pertama adalah header
        $headers = array_shift($rows);
        $result = [];

        foreach ($rows as $row) {
            $rowData = [];
            foreach ($headers as $index => $header) {
                $rowData[$header] = $row[$index] ?? null;
            }
            $result[] = $rowData;
        }

        return $result;
    }

    /**
     * Update single cell
     *
     * @param string $sheetName
     * @param string $cell Cell reference (contoh: 'A2')
     * @param mixed $value
     * @return bool
     */
    public function updateCell(string $sheetName, string $cell, $value): bool
    {
        try {
            $range = "{$sheetName}!{$cell}";
            $values = [[$value]];

            return $this->updateRange($sheetName, $cell, $values);

        } catch (Exception $e) {
            Log::error('Error updating cell', [
                'sheet' => $sheetName,
                'cell' => $cell,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Update range of cells
     *
     * @param string $sheetName
     * @param string $range Range (contoh: 'A2:S2')
     * @param array $values 2D array of values
     * @return bool
     */
    public function updateRange(string $sheetName, string $range, array $values): bool
    {
        try {
            $fullRange = "{$sheetName}!{$range}";

            $body = new ValueRange([
                'values' => $values
            ]);

            $params = [
                'valueInputOption' => 'RAW'
            ];

            $result = $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $fullRange,
                $body,
                $params
            );

            Log::info('Range updated successfully', [
                'sheet' => $sheetName,
                'range' => $range,
                'updated_cells' => $result->getUpdatedCells(),
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error updating range', [
                'sheet' => $sheetName,
                'range' => $range,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Append new row ke akhir sheet
     *
     * @param string $sheetName
     * @param array $rowData Array of values untuk satu baris
     * @return bool
     */
    public function appendRow(string $sheetName, array $rowData): bool
    {
        try {
            $body = new ValueRange([
                'values' => [$rowData]
            ]);

            $params = [
                'valueInputOption' => 'RAW'
            ];

            $result = $this->service->spreadsheets_values->append(
                $this->spreadsheetId,
                $sheetName,
                $body,
                $params
            );

            Log::info('Row appended successfully', [
                'sheet' => $sheetName,
                'updated_range' => $result->getUpdates()->getUpdatedRange(),
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error appending row', [
                'sheet' => $sheetName,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Batch update multiple ranges
     *
     * @param array $updates Array of ['range' => '...', 'values' => [...]]
     * @return bool
     */
    public function batchUpdate(array $updates): bool
    {
        try {
            $data = [];
            foreach ($updates as $update) {
                $data[] = new ValueRange([
                    'range' => $update['range'],
                    'values' => $update['values'],
                ]);
            }

            $body = new \Google\Service\Sheets\BatchUpdateValuesRequest([
                'valueInputOption' => 'RAW',
                'data' => $data,
            ]);

            $result = $this->service->spreadsheets_values->batchUpdate(
                $this->spreadsheetId,
                $body
            );

            Log::info('Batch update successful', [
                'total_updated_cells' => $result->getTotalUpdatedCells(),
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error batch updating', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get spreadsheet metadata (sheet names, etc)
     *
     * @return array
     */
    public function getSpreadsheetInfo(): array
    {
        try {
            $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);

            $sheets = [];
            foreach ($spreadsheet->getSheets() as $sheet) {
                $properties = $sheet->getProperties();
                $sheets[] = [
                    'title' => $properties->getTitle(),
                    'sheet_id' => $properties->getSheetId(),
                    'index' => $properties->getIndex(),
                    'row_count' => $properties->getGridProperties()->getRowCount(),
                    'column_count' => $properties->getGridProperties()->getColumnCount(),
                ];
            }

            return [
                'spreadsheet_id' => $spreadsheet->getSpreadsheetId(),
                'title' => $spreadsheet->getProperties()->getTitle(),
                'sheets' => $sheets,
            ];

        } catch (Exception $e) {
            Log::error('Error getting spreadsheet info', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Test koneksi ke Google Sheets
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $this->getSpreadsheetInfo();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
