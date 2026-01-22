<?php

namespace App\Console\Commands;

use App\Models\RekomendasiAplikasiForm;
use App\Models\RekomendasiVerifikasi;
use Illuminate\Console\Command;

class BackfillRekomendasiVerifikasi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rekomendasi:backfill-verifikasi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill verifikasi records for existing proposals with status "diajukan"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting backfill of verifikasi records...');

        // Find all proposals with status 'diajukan' that don't have verifikasi records
        $proposals = RekomendasiAplikasiForm::where('status', 'diajukan')
            ->doesntHave('verifikasi')
            ->get();

        if ($proposals->isEmpty()) {
            $this->info('No proposals found that need backfilling.');
            return Command::SUCCESS;
        }

        $this->info("Found {$proposals->count()} proposals to backfill.");

        $bar = $this->output->createProgressBar($proposals->count());
        $bar->start();

        $successCount = 0;
        $failureCount = 0;

        foreach ($proposals as $proposal) {
            try {
                RekomendasiVerifikasi::create([
                    'rekomendasi_aplikasi_form_id' => $proposal->id,
                    'verifikator_id' => null,
                    'status' => 'menunggu',
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $this->error("\nFailed to create verifikasi for proposal {$proposal->ticket_number}: {$e->getMessage()}");
                $failureCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Backfill completed!");
        $this->info("Successfully created: {$successCount} verifikasi records");

        if ($failureCount > 0) {
            $this->error("Failed: {$failureCount} records");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
