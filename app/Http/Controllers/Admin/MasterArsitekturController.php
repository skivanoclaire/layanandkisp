<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MasterArsitekturExport;
use App\Http\Controllers\Controller;
use App\Models\MasterArsitektur\Item;
use App\Models\UnitKerja;
use App\Services\MasterArsitektur\Dasbor;
use App\Services\MasterArsitektur\Importer;
use App\Services\MasterArsitektur\Skema;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

/**
 * Master Data Arsitektur (khusus role Admin): dasbor per kategori, CRUD, impor, ekspor.
 * Susunan data diatur di config/master_arsitektur.php.
 */
class MasterArsitekturController extends Controller
{
    public function __construct(private Dasbor $dasbor)
    {
    }

    public function index()
    {
        return redirect()->route('admin.master-arsitektur.kategori', array_key_first(Skema::kategori()));
    }

    public function kategori(Request $request, string $kategori)
    {
        $konfig = Skema::kategori($kategori);
        $filter = $this->dasbor->filterDari($kategori, $request->query());
        $query = $this->dasbor->query($kategori, $filter);

        $panel = $konfig['panel'];
        $sebaran = [];
        foreach (['ref_1', 'ref_2'] as $kolom) {
            if (isset($panel[$kolom])) {
                $sebaran[$kolom] = $this->dasbor->sebaran($query, $kolom);
            }
        }

        $daftarOpd = isset($panel['opd']) ? $this->dasbor->daftarOpd($query) : collect();

        return view('admin.master-arsitektur.index', [
            'kategori' => $kategori,
            'konfig' => $konfig,
            'semuaKategori' => Skema::kategori(),
            'jumlahKategori' => $this->dasbor->jumlahPerKategori(),
            'daftarFilter' => $this->dasbor->daftarFilter($kategori),
            'pilihan' => $this->dasbor->pilihan($kategori, $filter),
            'filter' => $filter,
            'total' => (clone $query)->count(),
            'status' => $this->dasbor->sebaran($query, 'status'),
            'indikator' => $this->dasbor->indikator($kategori, $query),
            'daftarOpd' => $daftarOpd,
            'sebaran' => $sebaran,
            'katalog' => (clone $query)->with('unitKerja')
                ->orderBy('jenis')->orderBy('kode')->orderBy('nama')
                ->paginate(25)->withQueryString(),
        ]);
    }

    public function ekspor(Request $request, string $kategori)
    {
        $filter = $this->dasbor->filterDari($kategori, $request->query());
        $jenis = isset($filter['jenis']) ? [$filter['jenis']] : Skema::kategori($kategori)['jenis'];

        $items = $this->dasbor->query($kategori, $filter)->with('unitKerja')
            ->orderBy('kode')->orderBy('nama')->get();

        return Excel::download(
            new MasterArsitekturExport(array_values(array_filter($jenis, [Skema::class, 'adaJenis'])), $items),
            'Master-Data-Arsitektur-' . $kategori . '-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function create(Request $request, string $kategori)
    {
        $daftarJenis = Skema::kategori($kategori)['jenis'];
        $jenis = in_array($request->query('jenis'), $daftarJenis, true) ? $request->query('jenis') : $daftarJenis[0];

        return $this->form(new Item(['jenis' => $jenis]), $kategori);
    }

    public function store(Request $request, string $kategori)
    {
        $jenis = $request->input('jenis');
        abort_unless(in_array($jenis, Skema::kategori($kategori)['jenis'], true), 422, 'Jenis data tidak sesuai kategori.');

        $item = new Item(['jenis' => $jenis]);
        $this->simpan($request, $item);

        return redirect()->route('admin.master-arsitektur.show', $item)
            ->with('success', Skema::label($jenis) . ' berhasil ditambahkan.');
    }

    public function show(Item $item)
    {
        $item->load('unitKerja');

        return view('admin.master-arsitektur.show', [
            'item' => $item,
            'kategori' => Skema::kategoriDari($item->jenis),
            'kolom' => Skema::kolom($item->jenis),
        ]);
    }

    public function edit(Item $item)
    {
        return $this->form($item, Skema::kategoriDari($item->jenis));
    }

    public function update(Request $request, Item $item)
    {
        $this->simpan($request, $item);

        return redirect()->route('admin.master-arsitektur.show', $item)
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        $kategori = Skema::kategoriDari($item->jenis);
        $item->delete();

        return redirect()->route('admin.master-arsitektur.kategori', $kategori)
            ->with('success', 'Data berhasil dihapus.');
    }

    public function formImpor()
    {
        return view('admin.master-arsitektur.impor', [
            'semuaKategori' => Skema::kategori(),
            'semuaJenis' => Skema::jenis(),
        ]);
    }

    public function impor(Request $request, Importer $importer)
    {
        $data = $request->validate([
            'berkas' => ['required', 'file', 'max:51200', 'extensions:xlsx,xls,csv'],
            'jenis' => ['nullable', Rule::in(array_keys(Skema::jenis()))],
            'mode' => ['required', Rule::in([Importer::MODE_PERBARUI, Importer::MODE_GANTI])],
        ], [
            'berkas.extensions' => 'Berkas harus berformat .xlsx, .xls, atau .csv.',
        ]);

        $berkas = $request->file('berkas');

        try {
            $hasil = $importer->impor(
                $berkas->getRealPath(),
                $berkas->getClientOriginalExtension(),
                $data['jenis'] ?? null,
                $data['mode'],
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            return back()->withInput()->with('error', 'Berkas tidak dapat dibaca: ' . $e->getMessage());
        }

        return redirect()->route('admin.master-arsitektur.impor.form')
            ->with('hasil_impor', $hasil)
            ->with('success', 'Impor selesai.');
    }

    private function form(Item $item, string $kategori)
    {
        $jenis = $item->jenis;
        $saran = [];

        // Saran isian diambil dari nilai yang sudah ada pada jenis yang sama
        foreach (['status', 'ref_1', 'ref_2', 'ref_3'] as $kolom) {
            if (isset(Skema::kolom($jenis)[$kolom])) {
                $saran[$kolom] = Item::where('jenis', $jenis)->whereNotNull($kolom)
                    ->distinct()->orderBy($kolom)->limit(500)->pluck($kolom);
            }
        }

        return view('admin.master-arsitektur.form', [
            'item' => $item,
            'jenis' => $jenis,
            'kategori' => $kategori,
            'konfigKategori' => Skema::kategori($kategori),
            'kolom' => Skema::kolom($jenis),
            'saran' => $saran,
            'instansi' => UnitKerja::where(fn ($q) => $q->where('is_active', true)->orWhere('id', $item->unit_kerja_id))
                ->orderBy('tipe')->orderBy('nama')->get(['id', 'nama', 'tipe'])->groupBy('tipe'),
        ]);
    }

    private function simpan(Request $request, Item $item): void
    {
        $kolom = Skema::kolom($item->jenis);
        $batas = ['kode' => 150, 'nama' => 1000, 'status' => 100, 'ref_1' => 255, 'ref_2' => 255, 'ref_3' => 255, 'tahun' => 20];

        $rules = [];
        $nama = [];
        foreach ($kolom as $kunci => $definisi) {
            if ($kunci === 'unit_kerja') {
                $rules['unit_kerja_id'] = ['nullable', 'integer', 'exists:unit_kerjas,id'];
                $nama['unit_kerja_id'] = $definisi['label'];
            } elseif (isset($batas[$kunci])) {
                $rules[$kunci] = [$kunci === 'nama' ? 'required' : 'nullable', 'string', 'max:' . $batas[$kunci]];
                $nama[$kunci] = $definisi['label'];
            } else {
                $rules["atribut.$kunci"] = ['nullable', 'string', 'max:20000'];
                $nama["atribut.$kunci"] = $definisi['label'];
            }
        }

        $data = $request->validate($rules, [], $nama);

        foreach (array_keys($batas) as $kunci) {
            if (isset($kolom[$kunci])) {
                $item->setAttribute($kunci, $data[$kunci] ?? null);
            }
        }

        if (isset($kolom['unit_kerja'])) {
            $unitKerjaId = isset($data['unit_kerja_id']) ? (int) $data['unit_kerja_id'] : null;

            // Nama pada sumber hanya diganti bila perangkat daerahnya memang diubah
            if ($unitKerjaId !== $item->unit_kerja_id) {
                $item->unit_kerja_id = $unitKerjaId;
                $item->unit_kerja_teks = $unitKerjaId ? UnitKerja::find($unitKerjaId)?->nama : null;
            }
        }

        $atribut = array_filter($data['atribut'] ?? [], fn ($v) => $v !== null && $v !== '');
        $item->atribut = $atribut ?: null;

        $item->save();
    }
}
