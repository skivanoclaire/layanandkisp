<?php

namespace App\Console\Commands;

use App\Services\MasterArsitektur\Importer;
use Illuminate\Console\Command;
use RuntimeException;

class ImporMasterArsitekturCommand extends Command
{
    protected $signature = 'master-arsitektur:impor
        {berkas : Lokasi berkas .xlsx/.xls/.csv}
        {--jenis= : Jenis data (kunci pada config master_arsitektur.jenis); kosong = semua sheet yang dikenali}
        {--ganti : Hapus data lama pada jenis yang diimpor sebelum mengisi ulang}';

    protected $description = 'Impor Master Data Arsitektur dari Excel/CSV';

    public function handle(Importer $importer): int
    {
        $berkas = $this->argument('berkas');

        if (! is_file($berkas)) {
            $this->error("Berkas tidak ditemukan: {$berkas}");

            return self::FAILURE;
        }

        try {
            $hasil = $importer->impor(
                $berkas,
                pathinfo($berkas, PATHINFO_EXTENSION),
                $this->option('jenis') ?: null,
                $this->option('ganti') ? Importer::MODE_GANTI : Importer::MODE_PERBARUI,
            );
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Jenis data', 'Sheet', 'Dibaca', 'Ditambah', 'Diperbarui', 'Dihapus', 'Baris kembar', 'Catatan'],
            array_map(fn ($h) => [
                $h['label'], $h['sheet'], $h['dibaca'], $h['ditambah'], $h['diperbarui'], $h['dihapus'], $h['duplikat'], $h['galat'] ?? '',
            ], $hasil)
        );

        $tanpaInstansi = [];
        foreach ($hasil as $h) {
            foreach ($h['tanpa_instansi'] as $nama => $jumlah) {
                $tanpaInstansi[$nama] = ($tanpaInstansi[$nama] ?? 0) + $jumlah;
            }
        }

        if ($tanpaInstansi) {
            arsort($tanpaInstansi);
            $this->warn('Nama perangkat daerah yang tidak ditemukan di Master Data Unit Kerja:');
            foreach ($tanpaInstansi as $nama => $jumlah) {
                $this->line("  - {$nama} ({$jumlah} baris)");
            }
        }

        return self::SUCCESS;
    }
}
