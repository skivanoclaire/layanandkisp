<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VidconData;
use Illuminate\Support\Facades\File;

class VidconDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to CSV file
        $csvFile = database_path('seeders/data/vidcon_data.csv');

        if (!File::exists($csvFile)) {
            $this->command->error("CSV file not found at: {$csvFile}");
            $this->command->info("Please place your CSV file at: database/seeders/data/vidcon_data.csv");
            return;
        }

        // Open and read CSV file
        $file = fopen($csvFile, 'r');

        // Skip header row
        $header = fgetcsv($file);

        $count = 0;
        $skipped = 0;
        $lineNumber = 1; // Start at 1 because we already read header

        // Read each row
        while (($row = fgetcsv($file)) !== false) {
            $lineNumber++;

            // Skip completely empty rows
            if (empty(array_filter($row))) {
                $this->command->warn("Skipping empty row at line {$lineNumber}");
                $skipped++;
                continue;
            }

            // CSV has empty first column, so indices are shifted by 1
            $cleanData = [
                'no' => $this->cleanNumber($row[1] ?? null), // Column B (index 1)
                'nama_instansi' => $this->cleanString($row[3] ?? null), // Column D
                'nomor_surat' => $this->cleanString($row[4] ?? null), // Column E
                'judul_kegiatan' => $this->cleanString($row[5] ?? null), // Column F
                'lokasi' => $this->cleanString($row[6] ?? null), // Column G
                'tanggal_mulai' => $this->parseIndonesianDate($row[7] ?? null), // Column H
                'tanggal_selesai' => $this->parseIndonesianDate($row[8] ?? null), // Column I
                'jam_mulai' => $this->parseTime($row[9] ?? null), // Column J
                'jam_selesai' => $this->parseTime($row[10] ?? null), // Column K
                'platform' => $this->cleanString($row[11] ?? null), // Column L
                'operator' => $this->cleanString($row[12] ?? null), // Column M
                'dokumentasi' => $this->cleanString($row[13] ?? null), // Column N
                'akun_zoom' => $this->cleanString($row[14] ?? null), // Column O
                'informasi_pimpinan' => $this->cleanString($row[15] ?? null), // Column P
                'keterangan' => $this->cleanString($row[16] ?? null), // Column Q
            ];

            // Validate: must have at least nama_instansi or judul_kegiatan
            if (empty($cleanData['nama_instansi']) && empty($cleanData['judul_kegiatan'])) {
                $this->command->warn("Skipping invalid row at line {$lineNumber} - no instansi or kegiatan");
                $skipped++;
                continue;
            }

            try {
                VidconData::create($cleanData);
                $count++;
            } catch (\Exception $e) {
                $this->command->error("Error importing row {$lineNumber}: " . $e->getMessage());
                $skipped++;
            }
        }

        fclose($file);

        $this->command->info("Successfully imported {$count} vidcon data records.");
        if ($skipped > 0) {
            $this->command->warn("Skipped {$skipped} invalid or empty rows.");
        }
    }

    /**
     * Clean and trim string, return null if empty
     */
    private function cleanString($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $cleaned = trim($value);

        // Remove common garbage characters
        $cleaned = str_replace(["\r", "\n", "\t"], ' ', $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned); // Multiple spaces to single

        return $cleaned === '' ? null : $cleaned;
    }

    /**
     * Clean and convert to integer, return null if invalid
     */
    private function cleanNumber($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Remove non-numeric characters except minus
        $cleaned = preg_replace('/[^0-9-]/', '', trim($value));

        if ($cleaned === '' || $cleaned === '-') {
            return null;
        }

        return (int)$cleaned;
    }

    /**
     * Parse Indonesian date format like "Senin, 13 Januari 2025"
     */
    private function parseIndonesianDate($dateString)
    {
        if ($dateString === null || $dateString === '') {
            return null;
        }

        $dateString = trim($dateString);

        // Skip obvious non-dates
        if (strlen($dateString) < 6 || $dateString === '-') {
            return null;
        }

        // Indonesian month names
        $months = [
            'Januari' => '01', 'Februari' => '02', 'Maret' => '03',
            'April' => '04', 'Mei' => '05', 'Juni' => '06',
            'Juli' => '07', 'Agustus' => '08', 'September' => '09',
            'Oktober' => '10', 'November' => '11', 'Desember' => '12',
        ];

        // Pattern: "Senin, 13 Januari 2025" or "13 Januari 2025"
        if (preg_match('/(\d{1,2})\s+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+(\d{4})/i', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $monthName = ucfirst(strtolower($matches[2]));
            $year = $matches[3];

            if (isset($months[$monthName])) {
                $month = $months[$monthName];
                return "{$year}-{$month}-{$day}";
            }
        }

        // If Indonesian format fails, try standard formats
        return $this->parseDate($dateString);
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($dateString)
    {
        if ($dateString === null || $dateString === '') {
            return null;
        }

        $dateString = trim($dateString);

        // Skip obvious non-dates
        if (strlen($dateString) < 6 || $dateString === '-') {
            return null;
        }

        // Try different date formats
        $formats = [
            'Y-m-d',      // 2025-01-15
            'd/m/Y',      // 15/01/2025
            'd-m-Y',      // 15-01-2025
            'm/d/Y',      // 01/15/2025
            'd.m.Y',      // 15.01.2025
            'Y/m/d',      // 2025/01/15
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false && $date->format($format) === $dateString) {
                // Additional validation: check if year is reasonable
                $year = (int)$date->format('Y');
                if ($year >= 2020 && $year <= 2030) {
                    return $date->format('Y-m-d');
                }
            }
        }

        return null;
    }

    /**
     * Parse time from various formats
     */
    private function parseTime($timeString)
    {
        if ($timeString === null || $timeString === '') {
            return null;
        }

        $timeString = trim($timeString);

        // Skip obvious non-times
        if (strlen($timeString) < 3 || $timeString === '-') {
            return null;
        }

        // Skip text values like "Selesai", "Selesai'", etc.
        if (preg_match('/^[a-zA-Z]+/i', $timeString)) {
            return null;
        }

        // Convert dots to colons for Indonesian format (10.00 -> 10:00)
        $timeString = str_replace('.', ':', $timeString);

        // If format is like "10:00" without minutes, add them
        if (preg_match('/^(\d{1,2})$/', $timeString, $matches)) {
            $timeString = $matches[1] . ':00';
        }

        // Try different time formats
        $formats = [
            'H:i',        // 14:30
            'H:i:s',      // 14:30:00
            'h:i A',      // 02:30 PM
            'h:i:s A',    // 02:30:00 PM
        ];

        foreach ($formats as $format) {
            $time = \DateTime::createFromFormat($format, $timeString);
            if ($time !== false) {
                // Validate hours and minutes
                $hours = (int)$time->format('H');
                $minutes = (int)$time->format('i');
                if ($hours >= 0 && $hours <= 23 && $minutes >= 0 && $minutes <= 59) {
                    return $time->format('H:i');
                }
            }
        }

        return null;
    }
}
