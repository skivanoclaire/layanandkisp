<?php

namespace App\Console\Commands;

use App\Models\DtsenAccessToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Audit A-03: memindahkan berkas data DTSEN (dtsen-data) yang masih ada di disk publik
 * ke disk privat, lalu menghapus salinan publiknya. Aman dijalankan ulang.
 */
class AmankanBerkasDtsen extends Command
{
    protected $signature = 'dtsen:amankan-berkas {--dry-run : Hanya tampilkan yang akan dipindahkan}';

    protected $description = 'Pindahkan berkas data DTSEN dari disk publik ke disk privat';

    public function handle(): int
    {
        $dipindah = 0;
        $dilewati = 0;

        DtsenAccessToken::whereNotNull('file_path')->where('file_path', 'like', 'dtsen-data/%')
            ->orderBy('id')->get(['id', 'file_path'])
            ->each(function (DtsenAccessToken $token) use (&$dipindah, &$dilewati) {
                $path = $token->file_path;

                if (Storage::disk('local')->exists($path)) {
                    if (Storage::disk('public')->exists($path) && ! $this->option('dry-run')) {
                        Storage::disk('public')->delete($path);
                    }
                    $dilewati++;

                    return;
                }

                if (! Storage::disk('public')->exists($path)) {
                    $this->warn("Berkas tidak ditemukan (token {$token->id}): {$path}");
                    $dilewati++;

                    return;
                }

                if (! $this->option('dry-run')) {
                    Storage::disk('local')->put($path, Storage::disk('public')->get($path));
                    Storage::disk('public')->delete($path);
                }

                $this->line(($this->option('dry-run') ? '[dry-run] ' : '').'Dipindahkan: '.$path);
                $dipindah++;
            });

        $this->info("Selesai. Dipindah: {$dipindah}, dilewati: {$dilewati}.");

        return self::SUCCESS;
    }
}
