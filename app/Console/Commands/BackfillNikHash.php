<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Throwable;

class BackfillNikHash extends Command
{
    protected $signature = 'users:backfill-nik-hash
                            {--dry-run : Hanya tampilkan rencana tanpa menulis ke DB}';

    protected $description = 'Hitung & isi kolom nik_hash untuk semua user yang punya NIK. Laporkan duplikat NIK.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $this->info(($dry ? '[DRY-RUN] ' : '') . 'Mulai backfill nik_hash...');

        $total      = 0;
        $updated    = 0;
        $skipped    = 0;
        $errors     = 0;
        $seenHashes = [];     // hash => first user_id
        $duplicates = [];     // hash => [user_ids...]

        User::whereNotNull('nik')->orderBy('id')->lazy(200)->each(function (User $user) use (
            &$total, &$updated, &$skipped, &$errors, &$seenHashes, &$duplicates, $dry
        ) {
            $total++;
            try {
                $plain = $user->nik;
            } catch (Throwable $e) {
                $errors++;
                $this->warn("User {$user->id}: gagal decrypt NIK ({$e->getMessage()})");
                return;
            }

            $hash = User::hashNik($plain);
            if (!$hash) {
                $skipped++;
                return;
            }

            if (isset($seenHashes[$hash])) {
                $duplicates[$hash][] = $user->id;
            } else {
                $seenHashes[$hash] = $user->id;
                $duplicates[$hash] = [$user->id];
            }

            if ($user->nik_hash === $hash) {
                $skipped++;
                return;
            }

            if (!$dry) {
                // saveQuietly supaya saving observer tidak menimpa hash yang
                // baru saja kita set (observer akan menghasilkan hash sama, jadi
                // sebenarnya aman; quiet hanya untuk menghindari side-effect lain).
                $user->nik_hash = $hash;
                $user->saveQuietly();
            }
            $updated++;
        });

        $this->newLine();
        $this->info("Total user dengan NIK : {$total}");
        $this->info(($dry ? "Akan di-update     : " : "Updated            : ") . $updated);
        $this->info("Skipped (sudah ok)    : {$skipped}");
        if ($errors) {
            $this->warn("Errors decrypt NIK    : {$errors}");
        }

        $dupGroups = array_filter($duplicates, fn($ids) => count($ids) > 1);
        if (empty($dupGroups)) {
            $this->info('Tidak ada NIK duplikat. Aman menambahkan UNIQUE constraint berikutnya.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->warn('Ditemukan ' . count($dupGroups) . ' NIK duplikat. Resolve manual sebelum pasang UNIQUE:');
        foreach ($dupGroups as $hash => $ids) {
            $hashShort = substr($hash, 0, 12);
            $this->line("  hash={$hashShort}... → user_ids: " . implode(', ', $ids));
        }
        $this->newLine();
        $this->line('Saran: pertahankan satu user (biasanya yang Terverifikasi/SSO), pindahkan');
        $this->line('relasi (jabatan, requests, dsb) ke user yang dipertahankan, lalu hapus duplikat.');

        return Command::SUCCESS;
    }
}
