<?php

namespace App\Console\Commands;

use App\Models\WebMonitor;
use Illuminate\Console\Command;

class CheckWebsiteStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website:check-status
                            {--limit=50 : Maximum number of websites to check per run}
                            {--force : Check all websites regardless of last_checked_at}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check status of all monitored websites (excluding no-domain)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $force = $this->option('force');

        $this->info('Starting website status check...');
        $this->newLine();

        // Get websites to check (excluding no-domain)
        $query = WebMonitor::where('status', '!=', 'no-domain');

        // If not forced, only check websites that haven't been checked in the last 1 hour
        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('last_checked_at')
                  ->orWhere('last_checked_at', '<', now()->subHour());
            });
        }

        $websites = $query->orderBy('last_checked_at', 'asc')
                         ->limit($limit)
                         ->get();

        if ($websites->isEmpty()) {
            $this->info('No websites need checking at this time.');
            return 0;
        }

        $this->info("Checking {$websites->count()} website(s)...");
        $this->newLine();

        $bar = $this->output->createProgressBar($websites->count());
        $bar->start();

        $stats = [
            'total' => $websites->count(),
            'active' => 0,
            'inactive' => 0,
            'errors' => 0,
        ];

        foreach ($websites as $website) {
            try {
                $oldStatus = $website->status;

                // Check status
                $website->checkStatus();

                $newStatus = $website->fresh()->status;

                // Track statistics
                if (in_array($newStatus, ['active', 'up', 'online'])) {
                    $stats['active']++;
                } elseif (in_array($newStatus, ['inactive', 'down', 'offline'])) {
                    $stats['inactive']++;
                }

                // Log status change
                if ($oldStatus !== $newStatus) {
                    $this->newLine();
                    $this->warn("  Status changed: {$website->subdomain} ({$oldStatus} → {$newStatus})");
                    $bar->display();
                }

            } catch (\Exception $e) {
                $stats['errors']++;
                $this->newLine();
                $this->error("  Error checking {$website->subdomain}: {$e->getMessage()}");
                $bar->display();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Display summary
        $this->info('Check completed!');
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Checked', $stats['total']],
                ['Active (Up)', $stats['active']],
                ['Inactive (Down)', $stats['inactive']],
                ['Errors', $stats['errors']],
            ]
        );

        return 0;
    }
}
