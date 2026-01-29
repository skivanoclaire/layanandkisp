<?php

namespace App\Console\Commands;

use App\Models\RekomendasiAplikasiForm;
use Illuminate\Console\Command;

class ResetVerifikasiForDiajukan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rekomendasi:reset-verifikasi-diajukan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset verifikasi records for proposals with status "diajukan" but verifikasi status not "menunggu"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Finding proposals with status "diajukan" that need verifikasi reset...');

        // Find all proposals with status 'diajukan' that have verifikasi records not in 'menunggu' status
        $proposals = RekomendasiAplikasiForm::where('status', 'diajukan')
            ->whereHas('verifikasi', function ($query) {
                $query->where('status', '!=', 'menunggu');
            })
            ->with('verifikasi')
            ->get();

        if ($proposals->isEmpty()) {
            $this->info('No proposals found that need verifikasi reset.');
            return Command::SUCCESS;
        }

        $this->info("Found {$proposals->count()} proposals to reset.");

        if (!$this->confirm('Do you want to proceed with resetting these verifikasi records?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($proposals->count());
        $bar->start();

        $successCount = 0;
        $failureCount = 0;

        foreach ($proposals as $proposal) {
            try {
                $proposal->verifikasi->update([
                    'verifikator_id' => null,
                    'status' => 'menunggu',
                    'checklist_analisis_kebutuhan' => false,
                    'checklist_perencanaan' => false,
                    'checklist_manajemen_risiko' => false,
                    'checklist_anggaran' => false,
                    'checklist_timeline' => false,
                    'catatan_verifikasi' => null,
                    'tanggal_verifikasi' => null,
                ]);

                $this->newLine();
                $this->info("✓ Reset verifikasi for {$proposal->ticket_number}");

                $successCount++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("✗ Failed to reset verifikasi for {$proposal->ticket_number}: {$e->getMessage()}");
                $failureCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Reset completed!");
        $this->info("Successfully reset: {$successCount} verifikasi records");

        if ($failureCount > 0) {
            $this->error("Failed: {$failureCount} records");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
