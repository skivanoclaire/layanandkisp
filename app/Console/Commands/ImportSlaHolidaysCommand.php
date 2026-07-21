<?php

namespace App\Console\Commands;

use App\Services\Sla\NationalHolidayImporter;
use Illuminate\Console\Command;
use RuntimeException;

class ImportSlaHolidaysCommand extends Command
{
    protected $signature = 'sla:import-holidays {year? : Tahun yang diimpor, default tahun berjalan}';

    protected $description = 'Impor libur nasional & cuti bersama ke daftar libur SLA';

    public function handle(NationalHolidayImporter $importer): int
    {
        $year = (int) ($this->argument('year') ?: now()->year);

        $this->info("Mengambil daftar libur tahun {$year}...");

        try {
            $hasil = $importer->import($year);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            '%d ditambah, %d diperbarui, %d dihapus, %d dilewati (entri manual).',
            $hasil['ditambah'],
            $hasil['diperbarui'],
            $hasil['dihapus'],
            $hasil['dilewati'],
        ));

        return self::SUCCESS;
    }
}
