<?php

namespace App\Console\Commands;

use App\Models\WebMonitor;
use App\Models\SubdomainRequest;
use Illuminate\Console\Command;

class FixDuplicateSubdomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subdomain:fix-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix duplicate domain suffixes in subdomain fields';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking for duplicate domain suffixes...');
        $this->newLine();

        // Fix WebMonitor records
        $monitors = WebMonitor::where('subdomain', 'like', '%.kaltaraprov.go.id.kaltaraprov.go.id')->get();
        $this->info("Found {$monitors->count()} WebMonitor records with duplicate suffixes");

        foreach ($monitors as $monitor) {
            $original = $monitor->subdomain;
            $fixed = str_replace('.kaltaraprov.go.id.kaltaraprov.go.id', '.kaltaraprov.go.id', $original);
            $monitor->subdomain = $fixed;
            $monitor->save();
            $this->line("  Fixed: {$original} → {$fixed}");
        }

        $this->newLine();

        // Fix SubdomainRequest records
        $requests = SubdomainRequest::where('subdomain_requested', 'like', '%.kaltaraprov.go.id.kaltaraprov.go.id')->get();
        $this->info("Found {$requests->count()} SubdomainRequest records with duplicate suffixes");

        foreach ($requests as $request) {
            $original = $request->subdomain_requested;
            $fixed = str_replace('.kaltaraprov.go.id.kaltaraprov.go.id', '.kaltaraprov.go.id', $original);
            $request->subdomain_requested = $fixed;
            $request->save();
            $this->line("  Fixed: {$original} → {$fixed}");
        }

        $this->newLine();
        $this->info('✓ Cleanup complete!');

        return 0;
    }
}
