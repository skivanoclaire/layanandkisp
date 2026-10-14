<?php

namespace App\Exports;

use App\Models\MasterArsitektur\Item;
use App\Services\MasterArsitektur\Skema;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Ekspor Master Data Arsitektur: satu sheet per jenis data. Nama sheet dan judul
 * kolom mengikuti config, sehingga berkas hasil ekspor bisa langsung diimpor kembali.
 */
class MasterArsitekturExport implements WithMultipleSheets
{
    /**
     * @param  array<int, string>  $jenis
     * @param  Collection<int, Item>  $items
     */
    public function __construct(private array $jenis, private Collection $items)
    {
    }

    public function sheets(): array
    {
        $perJenis = $this->items->groupBy('jenis');
        $sheets = [];

        foreach ($this->jenis as $jenis) {
            $kolom = Skema::kolom($jenis);
            $rows = [array_map(fn ($k) => Skema::judulExcel($k), array_values($kolom))];

            foreach ($perJenis->get($jenis, []) as $item) {
                $nilai = $item->nilaiKolom();
                $nilai['unit_kerja'] ??= $item->unitKerja?->nama;

                $rows[] = array_map(fn ($kunci) => $nilai[$kunci] ?? null, array_keys($kolom));
            }

            $sheets[] = new MasterArsitekturSheet(Skema::jenis($jenis)['sheet'], $rows);
        }

        return $sheets;
    }
}
