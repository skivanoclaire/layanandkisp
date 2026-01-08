<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailAccount;
use App\Services\WhmApiService;
use Illuminate\Support\Facades\Log;

class SyncEmailAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync email accounts from WHM CPanel server';

    protected $whmApi;

    public function __construct(WhmApiService $whmApi)
    {
        parent::__construct();
        $this->whmApi = $whmApi;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting email accounts synchronization...');

        try {
            $emailData = $this->whmApi->getAllEmailAccountsData();

            if (!$emailData['success']) {
                $this->error('Failed to fetch data from WHM: ' . $emailData['message']);
                Log::error('Email sync failed', ['message' => $emailData['message']]);
                return 1;
            }

            $accounts = $emailData['accounts'];
            $syncedCount = 0;
            $errors = [];

            $this->info('Found ' . count($accounts) . ' email accounts from WHM');

            $progressBar = $this->output->createProgressBar(count($accounts));
            $progressBar->start();

            foreach ($accounts as $accountData) {
                try {
                    EmailAccount::updateOrCreate(
                        ['email' => $accountData['email']],
                        [
                            'domain' => $accountData['domain'],
                            'user' => $accountData['user'],
                            'disk_used' => $accountData['disk_used'],
                            'disk_quota' => $accountData['disk_quota'],
                            'diskused_readable' => $accountData['diskused_readable'],
                            'diskquota_readable' => $accountData['diskquota_readable'],
                            'suspended' => $accountData['suspended'],
                            'last_synced_at' => now(),
                        ]
                    );
                    $syncedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error syncing {$accountData['email']}: " . $e->getMessage();
                    Log::error("Email sync error: " . $e->getMessage());
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            $this->info("Successfully synced {$syncedCount} email accounts.");

            if (count($errors) > 0) {
                $this->warn("Encountered " . count($errors) . " errors during sync.");
                Log::warning('Email sync completed with errors', ['errors' => $errors]);
            }

            Log::info('Email sync completed successfully', [
                'synced' => $syncedCount,
                'errors' => count($errors)
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Error during synchronization: ' . $e->getMessage());
            Log::error('Email sync exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return 1;
        }
    }
}
