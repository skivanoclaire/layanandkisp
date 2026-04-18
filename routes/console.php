<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule website status checks every hour
Schedule::command('website:check-status')->hourly();

// Sync Cloudflare DNS records to web_monitors every hour.
// Skip status check karena `website:check-status` di atas sudah meng-handle.
// Jalankan di menit ke-30 supaya tidak menabrak check-status yang jalan di menit 0.
Schedule::command('cloudflare:sync --skip-status-check')->hourlyAt(30);
