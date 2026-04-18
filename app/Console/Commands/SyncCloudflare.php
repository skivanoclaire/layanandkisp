<?php

namespace App\Console\Commands;

use App\Services\CloudflareService;
use Illuminate\Console\Command;

class SyncCloudflare extends Command
{
    protected $signature = 'cloudflare:sync
                            {--skip-status-check : Lewati pengecekan status website setelah sync (akan di-handle oleh website:check-status)}';

    protected $description = 'Sinkronisasi DNS records Cloudflare ke tabel web_monitors';

    public function handle(CloudflareService $cloudflare): int
    {
        $this->info('Memulai sinkronisasi Cloudflare → web_monitors...');
        $startedAt = now();

        $stats = $cloudflare->syncWebMonitorsFromDns(
            checkStatus: ! $this->option('skip-status-check')
        );

        $duration = $startedAt->diffInSeconds(now());

        $this->newLine();
        $this->info('Sinkronisasi selesai dalam ' . $duration . ' detik.');
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Record baru (created)', $stats['created']],
                ['Record diupdate (updated)', $stats['updated']],
                ['Status dicek (checked)',   $stats['checked']],
            ]
        );

        return self::SUCCESS;
    }
}
