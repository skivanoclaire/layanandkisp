<?php

namespace App\Http\Controllers;

use App\Models\MasterArsitektur\Item;
use App\Services\MasterArsitektur\Dasbor;
use App\Services\MasterArsitektur\Skema;
use Illuminate\Http\Request;

/**
 * Halaman publik /arsitektur: tampilan hanya-lihat dari Master Data Arsitektur.
 * Kategori dan kolom yang boleh tampil diatur di config master_arsitektur.publik.
 */
class ArsitekturPublikController extends Controller
{
    public function __construct(private Dasbor $dasbor)
    {
    }

    public function index()
    {
        return redirect()->route('arsitektur.kategori', array_key_first($this->kategoriPublik()));
    }

    public function kategori(Request $request, string $kategori)
    {
        $konfig = Skema::kategori($kategori);
        $filter = $this->dasbor->filterDari($kategori, $request->query());

        // Entri yang belum terpetakan ke Master Data Unit Kerja adalah urusan internal admin
        if (($filter['opd'] ?? null) === Dasbor::BELUM_TERPETAKAN) {
            unset($filter['opd']);
        }

        $query = $this->dasbor->query($kategori, $filter);

        $panel = $konfig['panel'];
        $sebaran = [];
        foreach (['ref_1', 'ref_2'] as $kolom) {
            if (isset($panel[$kolom])) {
                $sebaran[$kolom] = $this->dasbor->sebaran($query, $kolom);
            }
        }

        $pilihan = $this->dasbor->pilihan($kategori, $filter);
        unset($pilihan['opd'][Dasbor::BELUM_TERPETAKAN]);

        return view('public.arsitektur.index', [
            'kategori' => $kategori,
            'konfig' => $konfig,
            'semuaKategori' => $this->kategoriPublik(),
            'jumlahKategori' => $this->dasbor->jumlahPerKategori(),
            'daftarFilter' => $this->dasbor->daftarFilter($kategori),
            'pilihan' => $pilihan,
            'filter' => $filter,
            'total' => (clone $query)->count(),
            'status' => $this->dasbor->sebaran($query, 'status'),
            'indikator' => $this->dasbor->indikator($kategori, $query),
            'daftarOpd' => isset($panel['opd']) ? $this->dasbor->daftarOpd($query) : collect(),
            'sebaran' => $sebaran,
            'katalog' => (clone $query)->with('unitKerja')
                ->orderBy('jenis')->orderBy('kode')->orderBy('nama')
                ->paginate(25)->withQueryString(),
        ]);
    }

    public function show(Item $item)
    {
        abort_unless(Skema::adaJenis($item->jenis), 404);

        $kategori = Skema::kategoriDari($item->jenis);
        abort_unless(array_key_exists($kategori, $this->kategoriPublik()), 404);

        $item->load('unitKerja');
        $tersembunyi = config('master_arsitektur.publik.kolom_tersembunyi.' . $item->jenis, []);

        return view('public.arsitektur.show', [
            'item' => $item,
            'kategori' => $kategori,
            'kolom' => array_diff_key(Skema::kolom($item->jenis), array_flip($tersembunyi)),
        ]);
    }

    /** Kategori yang dibuka untuk publik, menurut urutan tab dasbor. */
    private function kategoriPublik(): array
    {
        return array_intersect_key(Skema::kategori(), array_flip(config('master_arsitektur.publik.kategori')));
    }
}
