<?php

namespace App\Services\MasterArsitektur;

use App\Models\MasterArsitektur\Item;
use App\Models\UnitKerja;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Query dasbor Master Data Arsitektur: filter, pilihan filter, dan isi panel.
 */
class Dasbor
{
    /** Nilai filter OPD untuk entri yang perangkat daerahnya belum terpetakan. */
    public const BELUM_TERPETAKAN = 'x';

    /** Filter yang berlaku pada suatu kategori: kunci => label. */
    public function daftarFilter(string $kategori): array
    {
        $konfig = Skema::kategori($kategori);
        $filter = [];

        if (isset($konfig['panel']['opd'])) {
            $filter['opd'] = 'OPD';
        }
        if (count($konfig['jenis']) > 1) {
            $filter['jenis'] = 'Jenis';
        }

        return $filter + $konfig['filter'];
    }

    /** Ambil nilai filter dari query string, buang yang kosong/tidak berlaku. */
    public function filterDari(string $kategori, array $input): array
    {
        $filter = [];

        foreach ([...array_keys($this->daftarFilter($kategori)), 'q'] as $kunci) {
            $nilai = $input[$kunci] ?? null;
            if (is_string($nilai) && trim($nilai) !== '') {
                $filter[$kunci] = trim($nilai);
            }
        }

        return $filter;
    }

    /** Query entri pada kategori dengan filter diterapkan ($kecuali = filter yang diabaikan). */
    public function query(string $kategori, array $filter = [], ?string $kecuali = null): Builder
    {
        $query = Item::query()->whereIn('jenis', Skema::kategori($kategori)['jenis']);

        foreach ($filter as $kunci => $nilai) {
            if ($kunci === $kecuali) {
                continue;
            }

            match ($kunci) {
                'opd' => $nilai === self::BELUM_TERPETAKAN
                    ? $query->whereNull('unit_kerja_id')->whereNotNull('unit_kerja_teks')
                    : $query->where('unit_kerja_id', (int) $nilai),
                'q' => $query->where(function (Builder $q) use ($nilai) {
                    $q->where('nama', 'like', "%{$nilai}%")
                        ->orWhere('kode', 'like', "%{$nilai}%")
                        ->orWhere('unit_kerja_teks', 'like', "%{$nilai}%");
                }),
                default => $query->where($kunci, $nilai),
            };
        }

        return $query;
    }

    /**
     * Pilihan tiap filter beserta jumlah entrinya. Pilihan suatu filter dihitung
     * dengan filter lain tetap berlaku, sehingga mis. memilih RAB 1 mempersempit RAB 2.
     *
     * @return array<string, array<string, array{label: string, jumlah: int}>>
     */
    public function pilihan(string $kategori, array $filter): array
    {
        $pilihan = [];

        foreach (array_keys($this->daftarFilter($kategori)) as $kunci) {
            $query = $this->query($kategori, $filter, $kunci);

            if ($kunci === 'opd') {
                $pilihan[$kunci] = $this->pilihanOpd($query);

                continue;
            }

            $baris = $query->whereNotNull($kunci)
                ->selectRaw("$kunci as nilai, count(*) as jumlah")
                ->groupBy($kunci)->orderBy($kunci)
                ->pluck('jumlah', 'nilai');

            $pilihan[$kunci] = $baris->map(fn ($jumlah, $nilai) => [
                'label' => $kunci === 'jenis' ? Skema::label($nilai) : (string) $nilai,
                'jumlah' => (int) $jumlah,
            ])->all();
        }

        return $pilihan;
    }

    private function pilihanOpd(Builder $query): array
    {
        $opd = $this->daftarOpd($query);
        $pilihan = [];

        foreach ($opd->where('id', '!==', null) as $baris) {
            $pilihan[(string) $baris['id']] = ['label' => $baris['nama'], 'jumlah' => $baris['jumlah']];
        }

        $belum = $opd->where('id', null)->sum('jumlah');
        if ($belum > 0) {
            $pilihan[self::BELUM_TERPETAKAN] = ['label' => '(Belum terpetakan ke Master Data Unit Kerja)', 'jumlah' => $belum];
        }

        return $pilihan;
    }

    /**
     * Jumlah entri per perangkat daerah. Entri yang belum terpetakan
     * dikelompokkan menurut nama pada sumbernya (id = null).
     *
     * @return Collection<int, array{id: int|null, nama: string, jumlah: int}>
     */
    public function daftarOpd(Builder $query): Collection
    {
        $baris = (clone $query)
            ->where(fn (Builder $q) => $q->whereNotNull('unit_kerja_id')->orWhereNotNull('unit_kerja_teks'))
            ->selectRaw('unit_kerja_id, unit_kerja_teks, count(*) as jumlah')
            ->groupBy('unit_kerja_id', 'unit_kerja_teks')
            ->toBase()->get();

        $nama = UnitKerja::whereIn('id', $baris->pluck('unit_kerja_id')->filter()->unique())->pluck('nama', 'id');
        $hasil = [];

        foreach ($baris as $b) {
            $terpetakan = $b->unit_kerja_id !== null && isset($nama[$b->unit_kerja_id]);
            $kunci = $terpetakan ? 'id:' . $b->unit_kerja_id : 'teks:' . PencocokInstansi::normal($b->unit_kerja_teks);

            $hasil[$kunci] ??= [
                'id' => $terpetakan ? (int) $b->unit_kerja_id : null,
                'nama' => $terpetakan ? $nama[$b->unit_kerja_id] : (string) $b->unit_kerja_teks,
                'jumlah' => 0,
            ];
            $hasil[$kunci]['jumlah'] += (int) $b->jumlah;
        }

        return collect($hasil)->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    /** Jumlah entri per nilai suatu kolom (untuk grafik), terbanyak di atas. */
    public function sebaran(Builder $query, string $kolom): Collection
    {
        return (clone $query)->whereNotNull($kolom)
            ->selectRaw("$kolom as nilai, count(*) as jumlah")
            ->groupBy($kolom)->orderByDesc('jumlah')->orderBy($kolom)
            ->toBase()->get()
            ->map(fn ($b) => ['label' => (string) $b->nilai, 'jumlah' => (int) $b->jumlah]);
    }

    /**
     * Kartu indikator kategori (config "indikator"): jumlah entri yang kolomnya
     * bernilai tertentu dan persentasenya terhadap seluruh entri pada $query.
     *
     * @return array<int, array{label: string, keterangan: ?string, jumlah: int, total: int, persen: float}>
     */
    public function indikator(string $kategori, Builder $query): array
    {
        $total = (clone $query)->count();
        $hasil = [];

        foreach (Skema::kategori($kategori)['indikator'] ?? [] as $indikator) {
            $kolom = in_array($indikator['kolom'], Skema::INTI, true)
                ? $indikator['kolom']
                : 'atribut->' . $indikator['kolom'];
            $jumlah = (clone $query)->where($kolom, $indikator['nilai'])->count();

            $hasil[] = [
                'label' => $indikator['label'],
                'keterangan' => $indikator['keterangan'] ?? null,
                'jumlah' => $jumlah,
                'total' => $total,
                'persen' => $total > 0 ? round($jumlah / $total * 100, 1) : 0.0,
            ];
        }

        return $hasil;
    }

    /** Jumlah entri tiap kategori (untuk tab). */
    public function jumlahPerKategori(): array
    {
        $perJenis = Item::query()->selectRaw('jenis, count(*) as jumlah')->groupBy('jenis')->pluck('jumlah', 'jenis');
        $jumlah = [];

        foreach (Skema::kategori() as $kunci => $kategori) {
            $jumlah[$kunci] = (int) collect($kategori['jenis'])->sum(fn ($j) => $perJenis[$j] ?? 0);
        }

        return $jumlah;
    }
}
